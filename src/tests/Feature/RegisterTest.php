<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class RegisterTest extends TestCase
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
    public function testValidationFails()
    {
        $cases = [
            'required_fields_empty' => [
                [], ['name','email', 'password']
            ],
            'email_missing' => [
                [
                    'name' => 'カエサル',
                    'password' => 'password1234',
                    'password_confirmation' => 'password1234',
                ],
                ['email']
            ],
            'password_too_short' => [
                [
                    'name' => 'カエサル',
                    'email' => 'test@example.com',
                    'password' => 'short',
                    'password_confirmation' => 'short',
                ],
                ['password']
            ],
            'password_not_matching' => [
                [
                   'name' => 'カエサル',
                    'email' => 'test@example.com',
                    'password' => 'password1234',
                    'password_confirmation' => 'differentpassword', 
                ],
                ['password']
            ],
        ];

        foreach($cases as $case => [$input, $expectedErrors]) {
            $response = $this->postJson('/register', $input);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors($expectedErrors);
        }
    }

    public function testUserIsRegisteredSuccessfully()
    {
        $response = $this->post('/register', [
                    'name' => 'カエサル',
                    'email' => 'test@example.com',
                    'password' => 'password1234',
                    'password_confirmation' => 'password1234', 
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', [
            
            'email' => 'test@example.com',
        ]);
    }

    public function  testUserReceivesVerificationEmailUponRegistration()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'カエサル',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234', 
            ]);

        $response->assertRedirect('/email/verify');
        $user = User::where('email','test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_RedirectToMailHogFromVerificationGuide()
    {
        $user = User::factory()->unverified()->create();
        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');

        $response = $this->get('http://localhost:8025');
        $response->assertStatus(200);
        
    }
    public function testEmailVerificationRedirectsToAttendance()
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Event::fake();

        $response= $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertRedirect('/attendance');
    }

}

