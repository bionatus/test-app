<?php

namespace Tests\Unit\Constants;

use App\Constants\CacheKeys;
use PHPUnit\Framework\TestCase;

class CacheKeysTest extends TestCase
{
    /** @test */
    public function its_users_count_constant_is_a_specific_string()
    {
        $this->assertSame('users_count', CacheKeys::USERS_COUNT);
    }
}
