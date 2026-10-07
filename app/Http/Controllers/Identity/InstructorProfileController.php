<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstructorProfileRequest;
use App\Models\InstructorProfile;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InstructorProfileController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Identity/InstructorApplication', [
            'profile' => $request->user()?->instructorProfile?->only(['display_name', 'biography', 'status']),
        ]);
    }

    public function store(StoreInstructorProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        DB::transaction(function () use ($user, $request): void {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedUser->role === UserRole::User, 403);
            $profile = $lockedUser->instructorProfile()->withTrashed()->first();
            if ($profile !== null && ($profile->trashed() || $profile->status !== 'rejected')) {
                throw ValidationException::withMessages(['display_name' => 'Ya tienes una solicitud pendiente o aprobada.']);
            }

            $data = [...$request->safe()->only(['display_name', 'biography']), 'status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null];
            if ($profile !== null) {
                $profile->update($data);
            } else {
                $lockedUser->instructorProfile()->create($data);
            }
        });

        return redirect()->route('instructor.apply')->with('success', 'Solicitud enviada. Un administrador la revisará.');
    }

    public function index(Request $request): Response
    {
        Gate::authorize('admin');
        $data = $request->validate(['status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])]]);
        $status = $data['status'] ?? 'pending';

        return Inertia::render('Admin/InstructorApplications', [
            'profiles' => InstructorProfile::query()->with('user:id,name,email,role,status')
                ->where('status', $status)->whereHas('user')->orderByDesc('id')->paginate(15)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function approve(Request $request, InstructorProfile $instructorProfile): RedirectResponse
    {
        return $this->review($request, $instructorProfile, 'approved');
    }

    public function reject(Request $request, InstructorProfile $instructorProfile): RedirectResponse
    {
        return $this->review($request, $instructorProfile, 'rejected');
    }

    private function review(Request $request, InstructorProfile $profile, string $status): RedirectResponse
    {
        Gate::authorize('admin');
        $reviewer = $request->user();
        abort_unless($reviewer instanceof User, 403);

        DB::transaction(function () use ($profile, $reviewer, $status): void {
            $applicant = User::query()->whereKey($profile->user_id)->lockForUpdate()->firstOrFail();
            $application = InstructorProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            if ($application->status !== 'pending') {
                throw ValidationException::withMessages(['review' => 'Esta solicitud ya fue revisada.']);
            }
            if ($applicant->status !== 'active' || $applicant->role !== UserRole::User) {
                throw ValidationException::withMessages(['review' => 'La cuenta del solicitante no permite revisar esta solicitud.']);
            }
            $application->update(['status' => $status, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()]);
            if ($status === 'approved') {
                $applicant->role = UserRole::Instructor;
                $applicant->save();
            }
        });

        return back()->with('success', $status === 'approved' ? 'Instructor aprobado. Ya puede crear cursos.' : 'Solicitud rechazada.');
    }
}
