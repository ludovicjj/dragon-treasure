<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    // create db app_test and reset db app_test before each tests
    use ResetDatabase;

    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post('/api/users', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'username' => 'draggin_in_the_morning',
                    'password' => 'password',
                ]
            ])
            ->assertStatus(201)
            ->post('/login', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'password' => 'password',
                ]
            ])
            ->assertSuccessful()
        ;
    }

    public function testPatchToUpdateUser(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json'
                ],
                'json' => [
                    'username' => 'changed'
                ]
            ])
            ->assertStatus(200);
    }

    public function testTreasuresCannotBeStolen(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $dragonTreasure = DragonTreasureFactory::createOne(['owner' => $otherUser]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json'
                ],
                'json' => [
                    'username' => 'changed',
                    'dragonTreasures' => [
                        // $user try to steal $otherUser's treasure, blocked by validator TreasureAllowedOwnerChangeValidator
                        '/api/treasures/' . $dragonTreasure->getId()
                    ]
                ]
            ])
            ->assertStatus(422);
    }
}