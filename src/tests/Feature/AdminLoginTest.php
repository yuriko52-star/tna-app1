<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /*public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
     */

    public function testAdminLoginFailsWhenEmailIsMissing()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password5678',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
    public function testAdminLoginFailsWhenPasswordIsMissing()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admintest@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function testAdminLoginFailsWithInvalidCredentials()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'invalidpassword',
        ]);

        $response->assertSessionHasErrors();
    }
    public function testAdminCanLoginSuccessfully()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admintest@example.com',
            'password' => bcrypt('password5678'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admintest@example.com',
            'password' => 'password5678',
        ]);

        $this->assertAuthenticatedAs($admin ,'admin');
        $response->assertRedirect('/admin/attendance/list');
    }
}
