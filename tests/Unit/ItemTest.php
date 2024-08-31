<?php

namespace Tests\Unit;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use DatabaseTransactions;

    public function test_item_creation_works(): void
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(User::factory()->create());
        $itemData = Item::factory()->make()->toArray();
        $response = $this->postJson('api/items/', $itemData);

        $response->assertStatus(201)
            ->assertJsonFragment($itemData);
        $itemId = $response->json('data')['id'];
        // Assert the ticket exists in the database
        $this->assertDatabaseHas('items', [
            'id' => $itemId,
        ]);
    }

    public function test_item_update_works(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $itemData = Item::factory()->create()->toArray();
        $newItemData = Item::factory()->make()->toArray();
        unset($newItemData['id']);

        $this->putJson("/api/items/{$itemData['id']}", $newItemData);

        $this->assertDatabaseHas('items', [...$newItemData, 'id' => $itemData['id']]);
    }

    public function test_item_type_listing_works(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Item::factory()->create()->toArray();
        $response = $this->getJson('api/items');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'sku',
                        'name',
                        'description',
                        'price',
                        'status',
                        'stock',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'next',
                    'prev',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_item_delete_works(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $itemId = Item::factory()->create()->toArray()['id'];

        $deleteResponse = $this->deleteJson("/api/items/{$itemId}");
        $deleteResponse->assertStatus(204);

        $this->assertSoftDeleted('items', [
            'id' => $itemId,
        ]);
    }
}
