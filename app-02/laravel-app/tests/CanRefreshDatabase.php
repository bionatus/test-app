<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait CanRefreshDatabase
{
    use RefreshDatabase {
        RefreshDatabase::refreshDatabase as refreshDatabaseForSingleTest;
    }

    public function refreshDatabase()
    {
        // Silenced
    }
}
