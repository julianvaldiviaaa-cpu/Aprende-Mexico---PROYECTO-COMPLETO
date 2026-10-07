<?php

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

test('shows registration and login forms to guests', function () {
    $this->get(route('register'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Registration'));
    $this->get(route('login'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('registers a normal account and logs in immediately without verifying email', function () {
    Notification::fake();
    $this->post(route('register.store'), [
        'name' => 'Ana Pérez', 'email' => ' ANA@example.test ',
        'password' => 'Aprender!2026', 'password_confirmation' => 'Aprender!2026',
        'role' => 'admin', 'status' => 'suspended', 'is_platform_admin' => true, 'email_verified_at' => now(),
    ])->assertRedirect(route('dashboard'));

    $user = User::query()->sole();
    $this->assertAuthenticatedAs($user);
    expect($user->email)->toBe('ana@example.test');
    expect($user->role)->toBe(UserRole::User);
    expect($user->status)->toBe('active');
    expect($user->email_verified_at)->toBeNull();
    expect(Hash::check('Aprender!2026', $user->password))->toBeTrue();
    Notification::assertNothingSent();
    $this->get(route('dashboard'))->assertOk();
});

test('validates required registration fields in Spanish', function () {
    $this->post(route('register.store'), [])->assertSessionHasErrors([
        'name' => 'El nombre es obligatorio.', 'email' => 'El correo electrónico es obligatorio.', 'password' => 'La contraseña es obligatoria.',
    ]);
    $this->assertDatabaseCount('users', 0);
});

test('rejects registration with an existing normalized email', function () {
    User::factory()->create(['email' => 'ana@example.test']);
    $this->post(route('register.store'), [
        'name' => 'Otra persona', 'email' => ' ANA@example.test ', 'password' => 'Aprender!2026', 'password_confirmation' => 'Aprender!2026',
    ])->assertSessionHasErrors(['email' => 'Este correo electrónico ya está registrado.']);
    $this->assertDatabaseCount('users', 1);
});

test('rejects invalid registration passwords', function (string $password, string $confirmation, string $message) {
    $this->post(route('register.store'), [
        'name' => 'Ana', 'email' => 'ana@example.test', 'password' => $password, 'password_confirmation' => $confirmation,
    ])->assertSessionHasErrors(['password' => $message]);
    $this->assertDatabaseCount('users', 0);
})->with([
    'confirmation' => ['Aprender!2026', 'OtraClave!2026', 'Las contraseñas no coinciden.'],
    'short' => ['Ab1!', 'Ab1!', 'La contraseña debe tener al menos 8 caracteres.'],
    'no number' => ['Aprender!abc', 'Aprender!abc', 'La contraseña debe incluir al menos un número.'],
    'no symbol' => ['Aprender2026', 'Aprender2026', 'La contraseña debe incluir al menos un símbolo.'],
    'too long' => [str_repeat('A', 73).'1!', str_repeat('A', 73).'1!', 'La contraseña no puede exceder los 72 caracteres.'],
    'multibyte' => [str_repeat('á', 36).'1!', str_repeat('á', 36).'1!', 'La contraseña es demasiado larga. Usa menos caracteres.'],
]);

test('logs in an unverified account using normalized credentials', function () {
    $user = User::factory()->unverified()->create(['email' => 'ana@example.test']);
    $this->post(route('login.store'), ['email' => ' ANA@example.test ', 'password' => 'password', 'remember' => true])->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    $this->get(route('register'))->assertRedirect();
});

test('rejects incorrect credentials', function () {
    $user = User::factory()->create();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrect'])->assertSessionHasErrors(['email' => 'El correo o la contraseña son incorrectos.']);
    $this->assertGuest();
});

test('blocks repeated login failures for the same account', function () {
    $user = User::factory()->create();
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
    }
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toStartWith('Demasiados intentos.');
    $this->assertGuest();
});

test('denies login and existing authenticated sessions for suspended accounts', function () {
    $user = User::factory()->suspended()->create();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    $this->assertGuest();
});

test('ends the authenticated session', function () {
    $this->actingAs(User::factory()->create())->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest();
});

test('promotes only an existing active account through the console', function () {
    $user = User::factory()->create();
    $this->artisan('app:promote-admin', ['email' => $user->email])->assertSuccessful();
    expect($user->fresh()->role)->toBe(UserRole::Admin);
    $this->artisan('app:promote-admin', ['email' => 'missing@example.test'])->assertFailed();
});
