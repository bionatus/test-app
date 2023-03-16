<?php

namespace Tests\Unit\Jobs;

use App\Jobs\AssignPublicNameToUsers;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssignPublicNameToUsersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->usingInMemoryDatabase()) {
            Schema::rename(User::tableName(), 'backup');
        }
        Schema::create(User::tableName(), function(Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('public_name')->unique()->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        if (!$this->usingInMemoryDatabase()) {
            Schema::drop(User::tableName());
            Schema::rename('backup', User::tableName());
        }
        parent::tearDown();
    }

    protected function usingInMemoryDatabase()
    {
        $default = config('database.default');

        return config("database.connections.$default.database") === ':memory:';
    }

    /** @test */
    public function it_assign_public_name_to_users_that_have_it_empty()
    {
        User::flushEventListeners();

        User::create(['first_name' => 'Charles thomas', 'last_name' => 'Williams']);
        $john      = User::create(['first_name' => 'John', 'last_name' => 'Doe']);
        $johnClone = User::create(['first_name' => 'John', 'last_name' => 'Doe']);
        $custom    = User::create([
            'first_name'  => 'George',
            'last_name'   => 'Washington',
            'public_name' => 'CustomName',
        ]);

        $job = new AssignPublicNameToUsers();
        $job->handle();

        $this->assertDatabaseMissing(User::tableName(), ['public_name' => null]);
        $this->assertDatabaseHas(User::tableName(), ['public_name' => 'JohnDoe', 'id' => $john->getKey()]);
        $this->assertDatabaseHas(User::tableName(), ['public_name' => 'JohnDoe1', 'id' => $johnClone->getKey()]);
        $this->assertDatabaseHas(User::tableName(), ['public_name' => 'CustomName', 'id' => $custom->getKey()]);
    }
}
