<?php

namespace Tests\Unit\Traits;

use App\Models\Flag;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasFlagsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = User::factory()->create();
    }

    /** @test */
    public function it_gets_the_flags_from_an_instance()
    {
        $expectedFlags = Flag::factory()->usingModel($this->instance)->count($count = 3)->create();
        Flag::factory()->usingModel(Note::factory()->create())->count(2)->create();

        $flags = $this->instance->flags;

        $this->assertCount($count, $flags);
        $this->assertEqualsCanonicalizing($expectedFlags->pluck(Flag::keyName()), $flags->pluck(Flag::keyName()));
    }

    /** @test */
    public function it_checks_if_an_specific_flag_exist_in_an_instance()
    {
        Flag::factory()->usingModel($this->instance)->create([
            'name' => $name = 'flag-a',
        ]);

        $this->assertTrue($this->instance->hasFlag($name));
        $this->assertFalse($this->instance->hasFlag('flag-b'));
    }

    /** @test */
    public function it_adds_a_flag_to_an_instance()
    {
        $this->instance->flag($name = 'flag');

        $this->assertDatabaseHas(Flag::tableName(), [
            'name'           => $name,
            'flaggable_type' => Relation::getAliasByModel(get_class($this->instance)),
            'flaggable_id'   => $this->instance->getKey(),
        ]);
    }

    /** @test */
    public function it_does_not_add_a_flag_if_it_is_already_exist()
    {
        Flag::factory()->usingModel($this->instance)->create([
            'name' => $name = 'flag',
        ]);

        $this->instance->flag($name);

        $this->assertDatabaseCount(Flag::tableName(), 1);
        $this->assertDatabaseHas(Flag::tableName(), [
            'name'           => $name,
            'flaggable_type' => Relation::getAliasByModel(get_class($this->instance)),
            'flaggable_id'   => $this->instance->getKey(),
        ]);
    }

    /** @test */
    public function it_removes_a_flag_from_an_instance()
    {
        Flag::factory()->usingModel($this->instance)->create([
            'name' => $name = 'flag',
        ]);

        $this->instance->unflag($name);

        $this->assertDatabaseMissing(Flag::tableName(), [
            'name'           => $name,
            'flaggable_type' => Relation::getAliasByModel(get_class($this->instance)),
            'flaggable_id'   => $this->instance->getKey(),
        ]);
    }
}
