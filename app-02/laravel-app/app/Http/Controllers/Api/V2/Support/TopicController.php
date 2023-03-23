<?php

namespace App\Http\Controllers\Api\V2\Support;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Support\Topic\BaseResource;
use App\Models\Topic;

class TopicController extends Controller
{
    public function index()
    {
        $page = Topic::with(['subject.tools', 'subtopics.subject.tools'])->paginate();

        return BaseResource::collection($page);
    }
}
