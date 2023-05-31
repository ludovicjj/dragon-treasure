<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\ResetDatabase;

class DragonTreasureResourceTest extends ApiTestCase
{
    // create db app_test and reset db app_test before each tests
    use ResetDatabase;

    public function testGetCollectionOfTreasures(): void
    {
        DragonTreasureFactory::createMany(5);

        $json = $this->browser()
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('length("hydra:member")', 5)
            ->json();

        $json->assertMatches('keys("hydra:member"[0])', [
            '@id',
            '@type',
            'name',
            'description',
            'value',
            'coolFactor',
            'owner',
            'shortDescription',
            'plunderedAtAgo',
        ]);

        // Same result as above with traditional assertSame (without jmespath)
        $this->assertSame(
            ['@id', '@type', 'name', 'description', 'value', 'coolFactor', 'owner', 'shortDescription', 'plunderedAtAgo'],
            array_keys($json->decoded()['hydra:member'][0])
        );
    }

    public function testPostToCreateTreasure(): void
    {
        $user = UserFactory::createOne(['password' => 'pass']);
        $this->browser()
            ->actingAs($user)
            ->post('/api/treasures', [
                'json' => []
            ])
            ->assertStatus(422)
            ->post('/api/treasures', [
                'json' => [
                    'name' => 'A shiny thing',
                    'description' => 'It sparkles when I wave it in the air.',
                    'value' => 1000,
                    'coolFactor' => 5,
                    'owner' => '/api/users/'.$user->getId()
                ]
            ])
            ->assertStatus(201)
            ->assertJsonMatches('name', 'A shiny thing');
    }

    public function testPostToCreateTreasureWithApiKey(): void
    {
        $token  = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_CREATE]
        ]);

        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken()
                ]
            ])
            ->assertStatus(422)
        ;
    }

    public function testPostToCreateTreasureDeniedWithoutScope(): void
    {
        $token  = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_USER_EDIT]
        ]);

        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken()
                ]
            ])
            ->assertStatus(403)
        ;
    }

    public function testPatchToUpdateTreasureByOwner(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne(['owner' => $user]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    'value' => 12345
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345);

        $user2 = UserFactory::createOne();
        $this->browser()
            ->actingAs($user2)
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    'value' => 6789
                ]
            ])
            ->assertStatus(403);
    }

    public function testPatchToUpdateOwnerTreasure(): void
    {
        $user = UserFactory::createOne();
        $user2 = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne(['owner' => $user]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    // change the owner to someone else blocked by securityPostDenormalize
                    'owner' => '/api/users/'. $user2->getId()
                ]
            ])
            ->assertStatus(403);
    }

    public function testAdminCanPatchToEditTreasure(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $treasure = DragonTreasureFactory::createOne();
        $this->browser()
            ->actingAs($admin)
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    'value' => 12345
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345);
    }
}