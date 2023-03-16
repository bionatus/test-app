<?php

namespace Tests\Unit\Models;

use App\Models\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

abstract class RelationsTestCase extends TestCase
{
    use RefreshDatabase;

    const COUNT = 10;
    protected Model $instance;

    protected function assertCorrectRelation(
        Collection $related,
        string $class,
        ?callable $callback = null,
        int $count = self::COUNT
    ): void {
        $this->assertCount($count, $related, "{$class} instances expected");
        if ($callback) {
            $this->assertCount($count, $related->filter($callback), "{$class} instances expected");
        }

        if (!$related->isEmpty()) {
            $this->assertCollectionOfClass($class, $related);
        }
    }
}
