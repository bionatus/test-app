<?php

namespace App\Jobs\Airtable;

use App;
use App\Lib\AirtableClient;
use App\Mail\CommonItem\SyncCommonItemReportEmail;
use App\Models\Item;
use App\Models\Supply;
use App\Models\Supply\Scopes\ByName;
use App\Models\SupplyCategory;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Validator;

class SyncCommonItems
{
    use Dispatchable, Queueable;

    const ROW_ID                        = 'id';
    const ROW_FIELDS                    = 'fields';
    const FIELD_ITEM                    = 'item';
    const FIELD_AIRTABLE_ID             = 'id';
    const FIELD_CATEGORY_1              = 'category_1';
    const FIELD_CATEGORY_2              = 'category_2';
    const FIELD_CATEGORY_3              = 'category_3';
    const FIELD_PARENT_ID               = 'parent_id';
    const FIELD_MAPPING_SUPPLY_CATEGORY = [
        self::FIELD_CATEGORY_1 => 'name',
    ];
    private int        $processedRecords = 0;
    private int        $malformedRecords = 0;
    private Collection $currentCategories;
    private Collection $errors;
    private Collection $createdIds;
    private Collection $updatedIds;

    public function __construct()
    {
        $this->errors     = Collection::make();
        $this->createdIds = Collection::make();
        $this->updatedIds = Collection::make();

        Config::set('airtable.table', Config::get('airtable.common_items_table'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AirtableClient $client)
    {
        $startedAt = Carbon::now();
        $client->setEndpoint(Config::get('airtable.endpoints.common_items') ?? '');

        $client->prepare([$this, 'parseResponse']);

        $mailable = new SyncCommonItemReportEmail($startedAt, Carbon::now(), $this->createdIds, $this->updatedIds,
            $this->errors->sort(), $this->processedRecords, $this->malformedRecords);
        $mailable->subject('Sync Common Items report');
        Mail::to(Config::get('mail.reports.sync'))->send($mailable);
    }

    public function parseResponse(array $records): void
    {
        $this->processedRecords  += count($records);
        $this->currentCategories = SupplyCategory::all();

        foreach ($records as $record) {
            if (!$this->isValidRecord($record)) {
                $this->malformedRecords += 1;
                continue;
            }

            $this->upsert($record[self::ROW_FIELDS]);
        }
    }

    public function processCategory($categoryOne, $categoryTwo, $categoryThree): int
    {
        $category = $this->getCategory($categoryOne);

        if ($categoryTwo) {
            $category = $this->getCategory($categoryTwo, $category);
        }

        if ($categoryThree) {
            $category = $this->getCategory($categoryTwo, $category);
        }

        return $category->id;
    }

    public function getCategory(string $name, SupplyCategory $parent = null): SupplyCategory
    {
        $query = $this->currentCategories->where(self::FIELD_MAPPING_SUPPLY_CATEGORY[self::FIELD_CATEGORY_1], $name);

        if ($parent) {
            $query->where(self::FIELD_PARENT_ID, $parent->id);
        }

        if (!$category = $query->first()) {
            $category = SupplyCategory::create([
                self::FIELD_MAPPING_SUPPLY_CATEGORY[self::FIELD_CATEGORY_1] => $name,
                self::FIELD_PARENT_ID                                       => $parent ? $parent->id : null,
            ]);
            $this->currentCategories->push($category);
        }

        return $category;
    }

    private function upsert(array $fields)
    {
        $fields[self::FIELD_ITEM] = preg_replace('/[ \n]+/', "", $fields[self::FIELD_ITEM]);

        if (!$supply = Supply::scoped(new ByName($fields[self::FIELD_ITEM]))->first()) {
            $supply     = new Supply();
            $supply->id = $supply->item()->create(['type' => Item::TYPE_SUPPLY])->getkey();
        }

        $supply->name               = $fields[self::FIELD_ITEM];
        $supply->internal_name      = $fields[self::FIELD_ITEM];
        $supply->supply_category_id = $this->processCategory($fields[self::FIELD_CATEGORY_1],
            $fields[self::FIELD_CATEGORY_2] ?? null, $fields[self::FIELD_CATEGORY_3] ?? null);

        if ($supply->save()) {
            $supply->wasRecentlyCreated ? $this->createdIds->push($supply->getKey()) : $this->updatedIds->push($supply->getKey());
        }

        return $supply;
    }

    private function isValidRecord(array $record): bool
    {
        $fields = $record[self::ROW_FIELDS] ?? [];
        $id     = $record[self::ROW_ID] ?? null;

        if (!$fields || !$id) {
            return false;
        }

        $validator = Validator::make($fields, $this->rules());

        if ($validator->fails()) {
            $key = $fields[self::FIELD_AIRTABLE_ID] ?? ('unknown (internal: ' . $id) . ')';
            $this->errors->put($key, $validator->errors());

            return false;
        }

        return true;
    }

    private function rules(): array
    {
        return [
            self::FIELD_ITEM       => ['required', 'max:255'],
            self::FIELD_CATEGORY_1 => ['required', 'max:255'],
            self::FIELD_CATEGORY_2 => ['nullable', 'max:255'],
            self::FIELD_CATEGORY_3 => ['nullable', 'max:255'],
        ];
    }
}
