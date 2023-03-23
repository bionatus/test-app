<?php

use App\Constants\MediaConversionNames;
use App\Models\Comment;
use App\Models\Post;

return [
    Comment::MORPH_ALIAS => [
        MediaConversionNames::THUMB => [
            'width'  => 400,
            'height' => 400,
        ],
    ],
    
    Post::MORPH_ALIAS => [
        MediaConversionNames::THUMB => [
            'width'  => 400,
            'height' => 400,
        ],
    ],
];
