<?php

namespace App\Jobs\SupplyCategory;

use App;
use App\Constants\Filesystem;
use App\Constants\MediaCollectionNames;
use App\Models\SupplyCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Storage;
use Str;

class SyncImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $categories = SupplyCategory::all();
        $categories->each(function(SupplyCategory $supplyCategory) {
            if (!$supplyCategory->hasMedia(MediaCollectionNames::IMAGES)) {
                $this->storeImage($supplyCategory);
            }
        });
    }

    private function storeImage(SupplyCategory $supplyCategory): void
    {
        $sourceDisk = Storage::disk(Filesystem::DISK_DEVELOPMENT_MEDIA);

        $fileName = $this->imageName($supplyCategory);
        $filePath = Filesystem::FOLDER_DEVELOPMENT_COMMON_ITEMS_CAT_IMAGES . $fileName;

        if ($sourceDisk->exists($filePath)) {
            $supplyCategory->addMediaFromDisk($filePath, Filesystem::DISK_DEVELOPMENT_MEDIA)
                ->preservingOriginal()
                ->toMediaCollection(MediaCollectionNames::IMAGES);
        }
    }

    private function imageName($supplyCategory): string
    {
        $name   = '';
        $parent = $supplyCategory->parent()->first();
        if ($parent) {
            $name = Str::slug($parent->name) . '-';
        }

        $name .= Str::slug($supplyCategory->name) . '.png';

        return $name;
    }
}
