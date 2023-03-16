<?php
/** @var \Carbon\CarbonInterface $startedAt */
/** @var \Carbon\CarbonInterface $completedAt */
/** @var int $processedRecords */
/** @var int $malformedRecords */
/** @var \Illuminate\Support\Collection $createdIds */
/** @var \Illuminate\Support\Collection $updatedIds */
/** @var \Illuminate\Support\Collection $errors */
?>

<h2>Common Items sync process</h2>
<h3>Times</h3>
<div>Started on: {{ $startedAt->toIso8601String() }}, completed {{ $completedAt->diffForHumans($startedAt) }}
    on {{ $completedAt->toIso8601String() }}</div>
<h3>Records</h3>
<div>Total processed: {{ $processedRecords }}</div>
@if($malformedRecords)
    <div>Malformed: {{ $malformedRecords }}</div>
@endif
<div>Created: {{ $createdIds->count() }}</div>
<div>Updated: {{ $updatedIds->count() }}</div>
<h3>Errors: {{ $errors->count() }}</h3>
@if($errors->isEmpty())
    <div>No errors detected</div>
@else
    <ul>
        @foreach($errors as $id => $fieldsMessages)
            <li>
                @if(Str::startsWith($id, 'unknown'))
                    <h4>Internal id: {{ $id }}</h4>
                @else
                    <h4>Airtable id: {{ $id }}</h4>
                @endif
                @php($messages = Illuminate\Support\Collection::make($fieldsMessages->getMessages())->flatten())
                <ul>
                    @foreach($messages as $message)
                        <li>
                            <div>{{$message}}</div>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
@endif
