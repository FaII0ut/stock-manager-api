<?php

namespace Tests\Unit;

use App\Models\Dispatch;
use App\Models\Item;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dispatch_creation_works(): void
    {
        $this->withoutExceptionHandling();
        // Sanctum::actingAs(User::factory()->create());
        $dispatchData = Dispatch::factory()->make()->toArray();
        Item::find($dispatchData['item_id'])->increment('stock', $dispatchData['quantity']);
        $response = $this->postJson('api/dispatches/', $dispatchData);

        $response->assertStatus(201);
        $dispatchId = $response->json('data')['id'];
        // Assert the ticket exists in the database
        $this->assertDatabaseHas('dispatches', [
            'id' =>  $response->json('data')['id'],
            'item_id' => $response->json('data')['item']['id'],
            'quantity' => $response->json('data')['quantity'],
            'staff_id' => $response->json('data')['staff']['id'],
        ]);
    }

    public function test_dispatch_update_works(): void
    {
        // Sanctum::actingAs(User::factory()->create());
        $dispatchData = Dispatch::factory()->create()->toArray();
        $newDispatchData = Dispatch::factory()->make()->toArray();
        unset($newDispatchData['id']);

        $this->putJson("/api/dispatches/{$dispatchData['id']}", $newDispatchData);

        $this->assertDatabaseHas('dispatches', [...$newDispatchData, 'id' => $dispatchData['id']]);
    }

    public function test_dispatch_type_listing_works(): void
    {
        // Sanctum::actingAs(User::factory()->create());
        Dispatch::factory()->create()->toArray();
        $response = $this->getJson('api/dispatches');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'staff',
                        'item',
                        'quantity',
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

    public function test_dispatch_delete_works(): void
    {
        // Sanctum::actingAs(User::factory()->create());
        $dispatchId = Dispatch::factory()->create()->toArray()['id'];

        $deleteResponse = $this->deleteJson("/api/dispatches/{$dispatchId}");
        $deleteResponse->assertStatus(204);

        $this->assertSoftDeleted('dispatches', [
            'id' => $dispatchId,
        ]);
    }
}
