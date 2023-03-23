<?php

namespace Tests\Unit\Models;

use App\Models\Model;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;

class ModelTest extends TestCase
{
    /** @test */
    public function it_should_call_load_count_method_if_model_has_not_count_attribute()
    {
        /** @var Model|Mock $model */
        $model = Mockery::mock(Model::class)->makePartial();
        $model->shouldReceive('getAttribute')->with('relation_count')->once()->andReturnNull();
        $model->shouldReceive('loadCount')->with('relation')->once()->andReturnSelf();

        $model->loadMissingCount('relation');
    }

    /** @test */
    public function it_should_not_call_load_count_method_if_model_has_count_attribute()
    {
        /** @var Model|Mock $model */
        $model = Mockery::mock(Model::class)->makePartial();
        $model->shouldReceive('getAttribute')->with('relation_count')->once()->andReturn(1);
        $model->shouldNotReceive('loadCount');

        $model->loadMissingCount('relation');
    }
}
