<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuildingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guests cannot store a building.
     */
    public function test_cannot_store_building_without_auth()
    {
        $response = $this->post('/admin/buildings', [
            'name' => 'อาคารทดสอบ ๕ ชั้น',
        ]);
        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated users can store building via JSON/AJAX.
     */
    public function test_authenticated_user_can_store_building_via_ajax()
    {
        $user = User::create([
            'user_id' => 'admin_ts',
            'name' => 'Test Admin',
            'email' => 'admin_ts@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->postJson('/admin/buildings', [
            'building_id' => 'B099',
            'name' => 'อาคารทดสอบ ๕ ชั้น',
            'floors' => 5,
            'description' => 'รายละเอียดอาคารทดสอบ',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $this->assertDatabaseHas('buildings', [
            'building_id' => 'B099',
            'building_name' => 'อาคารทดสอบ ๕ ชั้น',
            'floors' => 5,
            'description' => 'รายละเอียดอาคารทดสอบ',
        ]);
    }
}
