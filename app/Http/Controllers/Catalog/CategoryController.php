<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Category::class);

        return Inertia::render('Admin/Categories', [
            'categories' => Category::query()->withCount('courses')->orderBy('name')->orderBy('id')->paginate(20),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->safe()->only(['name', 'description']));

        return redirect()->route('categories.index')->with('success', 'Categoría creada.');
    }
}
