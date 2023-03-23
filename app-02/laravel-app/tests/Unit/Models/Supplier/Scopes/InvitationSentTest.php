<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\InvitationSent;
use App\Models\SupplierInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationSentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_determines_whether_to_send_the_invitation()
    {
        $user = User::factory()->create();

        Supplier::factory()->count(2)->createQuietly()->fresh();
        SupplierInvitation::factory()->usingUser($user)->count(3)->createQuietly()->pluck('supplier');

        $result = Supplier::scoped(new InvitationSent($user))->get();

        $result->take(2)->each(function(Supplier $supplier) {
            $this->assertFalse($supplier->invitation_sent);
        });

        $result->take(3)->skip(2)->each(function(Supplier $supplier) {
            $this->assertTrue($supplier->invitation_sent);
        });
    }
}
