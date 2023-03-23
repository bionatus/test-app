<?php

namespace App\Jobs\Airtable;

use App;
use App\Constants\GeocoderAccuracyValues;
use App\Lib\AirtableClient;
use App\Mail\Supplier\SyncReportEmail;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByAirtableId;
use App\Rules\UniqueString;
use Config;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Lang;
use Mail;
use Spatie\Geocoder\Exceptions\CouldNotGeocode;
use Spatie\Geocoder\Facades\Geocoder as GeocoderFacade;
use Spatie\Geocoder\Geocoder;
use Validator;

class SyncSuppliers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const ROW_ID                            = 'id';
    const ROW_FIELDS                        = 'fields';
    const FIELD_AIRTABLE_ID                 = 'Unique #';
    const FIELD_NAME                        = 'Name';
    const FIELD_STATE                       = 'State';
    const FIELD_COUNTRY                     = 'Country';
    const FIELD_BRANCH_EMAIL                = 'Branch Email';
    const FIELD_MAPPING                     = [
        self::FIELD_AIRTABLE_ID  => 'airtable_id',
        self::FIELD_NAME         => 'name',
        "Address"                => 'address',
        "Address 2"              => 'address_2',
        "City"                   => 'city',
        self::FIELD_STATE        => 'state',
        "Postal Code"            => 'zip_code',
        self::FIELD_COUNTRY      => 'country',
        "Lat"                    => 'latitude',
        "Lon"                    => 'longitude',
        "Phone Number"           => 'phone',
        self::FIELD_BRANCH_EMAIL => 'email',
        "Contact Name"           => 'contact_name',
        "Contact Email"          => 'contact_email',
        "Contact Job Title"      => 'contact_job',
        "Monday Hours"           => 'monday_hours',
        "Tuesday Hours"          => 'tuesday_hours',
        "Wednesday Hours"        => 'wednesday_hours',
        "Thursday Hours"         => 'thursday_hours',
        "Friday Hours"           => 'friday_hours',
        "Saturday Hours"         => 'saturday_hours',
        "Sunday Hours"           => 'sunday_hours',
    ];
    const ADDRESS_FIELDS_MAPPING            = [
        'Address',
        'City',
        'State',
        'Postal Code',
        'Country',
    ];
    const GEOCODER_WARNING_RESULT_NOT_FOUND = 'The geocoder service did not return a value for the address.';
    const GEOCODER_WARNING_BAD_ACCURACY     = 'The geocoder service did not return an accurate enough value. Accuracy = :accuracy';
    const GEOCODER_WARNING_EXCEPTION        = 'The geocoder service return the error message: :message';
    private int          $processedRecords = 0;
    private int          $malformedRecords = 0;
    private Collection   $errors;
    private Collection   $warnings;
    private Collection   $createdIds;
    private Collection   $updatedIds;
    private Collection   $deletedIds;
    private UniqueString $airtableIdUnique;
    private UniqueString $emailUnique;
    private Collection   $failedGeocodeIds;
    private bool         $updateCoordinates;

    public function __construct(bool $updateCoordinates = false)
    {
        $this->updateCoordinates = $updateCoordinates;

        $this->errors   = Collection::make();
        $this->warnings = Collection::make();

        $this->createdIds       = Collection::make();
        $this->updatedIds       = Collection::make();
        $this->deletedIds       = Collection::make();
        $this->failedGeocodeIds = Collection::make();
        $this->airtableIdUnique = new UniqueString();
        $this->emailUnique      = new UniqueString();

        Config::set('airtable.table', Config::get('airtable.suppliers_table'));
    }

    public function handle(AirtableClient $client)
    {
        $startedAt = Carbon::now();
        $client->setEndpoint(Config::get('airtable.endpoints.suppliers') ?? '');
        $client->prepare([$this, 'parseResponse']);

        $existingIds = $this->createdIds->merge($this->updatedIds);

        $toDeleteQuery    = DB::table(Supplier::tableName())->whereNotIn(Supplier::keyName(), $existingIds);
        $this->deletedIds = $toDeleteQuery->get()->pluck(Supplier::keyName());
        $toDeleteQuery->delete();

        $mailable = new SyncReportEmail($startedAt, Carbon::now(), $this->processedRecords, $this->createdIds,
            $this->updatedIds, $this->deletedIds, $this->failedGeocodeIds, $this->errors->sort(),
            $this->warnings->sort(), $this->malformedRecords);
        $mailable->subject('Sync stores report');
        Mail::to(Config::get('mail.reports.sync'))->send($mailable);
    }

    public function parseResponse(array $records): void
    {
        $this->processedRecords += count($records);

        foreach ($records as $record) {
            if (!$this->isValidRecord($record)) {
                $this->malformedRecords += 1;
                continue;
            }
            $this->upsert($record[self::ROW_FIELDS]);
        }
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

    private function upsert(array $fields): Supplier
    {
        $airtableId = $fields[self::FIELD_AIRTABLE_ID];
        $supplier      = Supplier::scoped(new ByAirtableId($airtableId))->first() ?? new Supplier();

        foreach (self::FIELD_MAPPING as $field => $attribute) {
            $value = $fields[$field] ?? null;

            if (self::FIELD_STATE === $field && !empty($fields[self::FIELD_COUNTRY]) && $value) {
                $value = $fields[self::FIELD_COUNTRY] . '-' . $value;
            }
            $supplier->setAttribute($attribute, $value);
        }

        if ($this->updateCoordinates) {
            try {
                $addressArray = [];
                foreach (self::ADDRESS_FIELDS_MAPPING as $field) {
                    if (array_key_exists($field, $fields)) {
                        $addressArray[] = $fields[$field];
                    }
                }
                $address = implode(', ', $addressArray);
                $geocode = GeocoderFacade::getCoordinatesForAddress($address);

                if ($geocode['accuracy'] == Geocoder::RESULT_NOT_FOUND) {
                    $this->warnings->put($fields[self::FIELD_AIRTABLE_ID],
                        new MessageBag(['Geocoder' => Lang::get(self::GEOCODER_WARNING_RESULT_NOT_FOUND)]));
                    throw new Exception();
                }

                if (in_array($geocode['accuracy'], GeocoderAccuracyValues::INVALID_VALUES)) {
                    $this->warnings->put($fields[self::FIELD_AIRTABLE_ID], new MessageBag([
                        'Geocoder' => Lang::get(self::GEOCODER_WARNING_BAD_ACCURACY,
                            ['accuracy' => $geocode['accuracy']]),
                    ]));
                    throw new Exception();
                }

                $supplier->latitude  = $geocode['lat'];
                $supplier->longitude = $geocode['lng'];
            } catch (CouldNotGeocode $couldNotGeocodeException) {
                $this->failedGeocodeIds->push($fields[self::FIELD_AIRTABLE_ID]);
                $this->warnings->put($fields[self::FIELD_AIRTABLE_ID], new MessageBag([
                    'Geocoder' => Lang::get(self::GEOCODER_WARNING_EXCEPTION,
                        ['message' => $couldNotGeocodeException->getMessage()]),
                ]));
            } catch (Exception $exception) {
                $this->failedGeocodeIds->push($fields[self::FIELD_AIRTABLE_ID]);
            }
        }

        $saved = $supplier->save();

        if ($saved) {
            $supplier->wasRecentlyCreated ? $this->createdIds->push($supplier->getKey()) : $this->updatedIds->push($supplier->getKey());
        }

        return $supplier;
    }

    private function rules(): array
    {
        return [
            self::FIELD_AIRTABLE_ID  => ['required', 'max:255', $this->airtableIdUnique],
            self::FIELD_NAME         => ['required', 'max:255'],
            self::FIELD_BRANCH_EMAIL => ['required', 'email:strict', 'ends_with_tld', $this->emailUnique],
        ];
    }
}
