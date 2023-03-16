<?php

namespace App\Http\Resources\Api\V3\Account;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AppVersionResource as BaseResource;
use App\Models\AppVersion;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AppVersionResource extends JsonResource implements HasJsonSchema
{
    private string $clientVersion;
    private User   $user;

    public function __construct(AppVersion $resource, string $clientVersion, User $user)
    {
        parent::__construct($resource);

        $this->clientVersion = $clientVersion;
        $this->user          = $user;
    }

    public function toArray($request)
    {
        $appVersion   = $this->resource;
        $needsUpdate  = $appVersion->needsUpdate($this->clientVersion);
        $needsConfirm = $appVersion->needsConfirm($this->clientVersion, $this->user);
        $resource     = (new BaseResource($appVersion))->toArray($request);

        $resource['requires_update']  = $needsUpdate;
        $resource['requires_confirm'] = $needsConfirm;

        return $resource;
    }

    public static function jsonSchema(): array
    {
        $modelResourceSchema = BaseResource::jsonSchema();

        return array_merge_recursive($modelResourceSchema, [
            'properties' => [
                'requires_update'  => ['type' => ['boolean']],
                'requires_confirm' => ['type' => ['boolean']],
            ],
            'required'   => [
                'requires_update',
                'requires_confirm',
            ],
        ]);
    }
}
