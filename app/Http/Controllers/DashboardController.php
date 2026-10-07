<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return Inertia::render('dashboard', [
            'courseCount' => $user->taughtCourses()->count(),
            'instructorProfile' => $user->instructorProfile?->only(['status', 'display_name']),
            'pendingApplications' => $user->isAdmin() ? InstructorProfile::query()->where('status', 'pending')->whereHas('user')->count() : null,
            'categoryCount' => $user->isAdmin() ? Category::query()->count() : null,
        ]);
    }
}
