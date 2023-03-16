<?php
/** @var \Illuminate\Support\Collection $records */
?>

<h2>Staff</h2><h3>Records</h3>
<div>Total processed: {{ $records->count() }}</div>

<div>
    @foreach($records as $record)
        ID: {{ $record['id'] }}<br>
        Email: {{ $record['email'] }}<br>
        Password: {{ $record['password'] }}
        <hr>
    @endforeach
</div>
