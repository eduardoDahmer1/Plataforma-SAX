<?php

namespace Tests\Feature;

use App\Mail\PasswordChangedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_password_security_page(): void
    {
        $this->get(route('user.password.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_change_password_and_receives_notification_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('SenhaAtual1')]);

        $this->actingAs($user)
            ->put(route('user.password.update'), [
                'current_password' => 'SenhaAtual1',
                'password' => 'NovaSenha2',
                'password_confirmation' => 'NovaSenha2',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user.password.edit'));

        $this->assertTrue(Hash::check('NovaSenha2', $user->fresh()->password));
        Mail::assertSent(PasswordChangedMail::class, fn (PasswordChangedMail $mail) => $mail->hasTo($user->email));
    }

    public function test_current_password_must_be_correct(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('SenhaAtual1')]);

        $this->actingAs($user)
            ->from(route('user.password.edit'))
            ->put(route('user.password.update'), [
                'current_password' => 'SenhaErrada1',
                'password' => 'NovaSenha2',
                'password_confirmation' => 'NovaSenha2',
            ])
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('user.password.edit'));

        $this->assertTrue(Hash::check('SenhaAtual1', $user->fresh()->password));
        Mail::assertNothingSent();
    }
}
