<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Note\BaseResource;
use App\Models\Note;
use App\Models\Note\Scopes\AlphabeticallyWithNullLast;
use App\Models\NoteCategory;

class NoteController extends Controller
{
    public function index(NoteCategory $noteCategory)
    {
        $notes = $noteCategory->notes()->scoped(new AlphabeticallyWithNullLast('sort'))->paginate();

        return BaseResource::collection($notes);
    }

    public function show(NoteCategory $noteCategory, Note $note)
    {
        return new BaseResource($note);
    }
}
