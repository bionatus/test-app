<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\WithNullPublicName;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WithNullPublicNameTest extends TestCase
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
    public function it_filters_users_with_null_public_name()
    {
        User::flushEventListeners();

        User::factory()->count(10)->make()->each(function(User $user) {
            User::create($user->only(['first_name', 'last_name', 'public_name']));
        });

        $withNullPublicNameCount = 5;
        User::factory()->count($withNullPublicNameCount)->make(['public_name' => null])->each(function(User $user) {
            User::create($user->only(['first_name', 'last_name', 'public_name']));
        });

        $filteredUsers = User::scoped(new WithNullPublicName())->get();

        $this->assertCount($withNullPublicNameCount, $filteredUsers);
    }
}
