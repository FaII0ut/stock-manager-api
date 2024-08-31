<?php

namespace Tests\Unit;

use App\Models\Staff;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use DatabaseTransactions;

    public function test_staff_creation_works(): void
    {
        $this->withoutExceptionHandling();
        // Sanctum::actingAs(User::factory()->create());
        $staffData = Staff::factory()->make()->toArray();
        $response = $this->postJson('api/staff/', $staffData);

        $response->assertStatus(201)
            ->assertJsonFragment($staffData);
        $staffId = $response->json('data')['id'];
        // Assert the ticket exists in the database
        $this->assertDatabaseHas('staff', [
            'id' => $staffId,
        ]);
    }

    public function test_staff_update_works(): void
    {
        // Sanctum::actingAs(User::factory()->create());
        $staffData = Staff::factory()->create()->toArray();
        $newStaffData = Staff::factory()->make()->toArray();
        unset($newStaffData['id']);

        $this->putJson("/api/staff/{$staffData['id']}", $newStaffData);

        $this->assertDatabaseHas('staff', [...$newStaffData, 'id' => $staffData['id']]);
    }

    public function test_staff_type_listing_works(): void
    {
        // Sanctum::actingAs(User::factory()->create());
        Staff::factory()->create()->toArray();
        $response = $this->getJson('api/staff');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'nid',
                        'name',
                        'phone',
                        'staff_code',
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

    public function test_staff_delete_works(): void
    {
        // Sanctum::actingAs(User::factory()->create());
        $staffId = Staff::factory()->create()->toArray()['id'];

        $deleteResponse = $this->deleteJson("/api/staff/{$staffId}");
        $deleteResponse->assertStatus(204);

        $this->assertSoftDeleted('staff', [
            'id' => $staffId,
        ]);
    }
}
