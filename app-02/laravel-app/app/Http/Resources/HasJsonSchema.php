<?php

namespace App\Http\Resources;

interface HasJsonSchema
{
    public static function jsonSchema(): array;
}
