<?php

use App\Models\InstructorProfile;
use App\Models\User;
use App\UserRole;
use Inertia\Testing\AssertableInertia as Assert;

test('requires login before requesting an instructor account', function () {
    $this->get(route('instructor.apply'))->assertRedirect(route('login'));
    $this->post(route('instructor.store'), [])->assertRedirect(route('login'));
});

test('stores a pending request owned by the authenticated user', function () {
    $user = User::factory()->unverified()->create();
    $otherUser = User::factory()->create();
    $this->actingAs($user)->post(route('instructor.store'), [
        'display_name' => 'Ana enseña', 'biography' => 'Quiero enseñar matemáticas.',
        'user_id' => $otherUser->id, 'status' => 'approved', 'reviewed_by' => $user->id,
    ])->assertRedirect(route('instructor.apply'));

    $profile = $user->instructorProfile()->sole();
    expect($profile->status)->toBe('pending');
    expect($profile->slug)->toBe('ana-ensena');
    expect($profile->reviewed_by)->toBeNull();
    expect($user->fresh()->role)->toBe(UserRole::User);
    $this->assertDatabaseCount('instructor_profiles', 1);
});

test('validates the instructor presentation', function () {
    $this->actingAs(User::factory()->create())->post(route('instructor.store'), [])->assertSessionHasErrors(['display_name' => 'Escribe tu nombre público.']);
    $this->assertDatabaseCount('instructor_profiles', 0);
});

test('prevents a second submission while the first is pending', function () {
    $profile = InstructorProfile::factory()->create();
    $this->actingAs($profile->user)->post(route('instructor.store'), ['display_name' => 'Otra solicitud'])->assertSessionHasErrors('display_name');
    $this->assertDatabaseCount('instructor_profiles', 1);
});

test('prevents an ordinary user from reviewing requests', function () {
    $profile = InstructorProfile::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('instructors.index'))->assertForbidden();
    $this->post(route('instructors.approve', $profile))->assertForbidden();
    $this->post(route('instructors.reject', $profile))->assertForbidden();
    expect($profile->fresh()->status)->toBe('pending');
});

test('approves a pending request and grants instructor access without email verification', function () {
    $this->freezeSecond();
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->unverified()->create();
    $profile = InstructorProfile::factory()->for($applicant)->create();
    $this->actingAs($admin)->from(route('instructors.index'))->post(route('instructors.approve', $profile))->assertRedirect(route('instructors.index'));

    expect($profile->fresh()->status)->toBe('approved');
    expect($profile->fresh()->reviewed_by)->toBe($admin->id);
    expect($profile->fresh()->reviewed_at->equalTo(now()))->toBeTrue();
    expect($applicant->fresh()->role)->toBe(UserRole::Instructor);
    $this->actingAs($applicant->refresh())->get(route('courses.create'))->assertOk();
});

test('rejects a request while preserving the normal account', function () {
    $admin = User::factory()->admin()->create();
    $profile = InstructorProfile::factory()->create();
    $this->actingAs($admin)->post(route('instructors.reject', $profile))->assertRedirect();
    expect($profile->fresh()->status)->toBe('rejected');
    expect($profile->user->fresh()->role)->toBe(UserRole::User);
});

test('resubmits a rejected request using the same profile and a new slug', function () {
    $profile = InstructorProfile::factory()->create(['status' => 'rejected', 'reviewed_by' => User::factory()->admin(), 'reviewed_at' => now()]);
    $this->actingAs($profile->user)->post(route('instructor.store'), ['display_name' => 'Nueva presentación', 'biography' => 'Nueva experiencia.'])->assertRedirect(route('instructor.apply'));
    expect($profile->fresh()->status)->toBe('pending');
    expect($profile->fresh()->reviewed_by)->toBeNull();
    expect($profile->fresh()->reviewed_at)->toBeNull();
    expect($profile->fresh()->slug)->toBe('nueva-presentacion');
    $this->assertDatabaseCount('instructor_profiles', 1);
});

test('prevents changing a previously reviewed decision', function () {
    $profile = InstructorProfile::factory()->approved()->create();
    $this->actingAs(User::factory()->admin()->create())->post(route('instructors.reject', $profile))->assertSessionHasErrors('review');
    expect($profile->fresh()->status)->toBe('approved');
});

test('lists filtered requests for administrators', function () {
    InstructorProfile::factory()->create();
    InstructorProfile::factory()->approved()->create();
    $this->actingAs(User::factory()->admin()->create())->get(route('instructors.index'))
        ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/InstructorApplications')->has('profiles.data', 1)->where('profiles.data.0.status', 'pending'));
    $this->get(route('instructors.index', ['status' => 'invalid']))->assertSessionHasErrors('status');
});

test('prevents instructors from requesting a second instructor account', function () {
    $this->actingAs(User::factory()->instructor()->create())->post(route('instructor.store'), ['display_name' => 'Otro perfil'])->assertForbidden();
});
