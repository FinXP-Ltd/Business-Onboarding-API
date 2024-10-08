<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Auth\User;

class UserModelTest extends TestCase
{

    /** @test */
    public function itShouldHaveBeneficiary()
    {
        $user = User::factory()->create();

        $this->assertEquals($user->count(), 1);

    }
}
