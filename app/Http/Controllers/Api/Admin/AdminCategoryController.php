<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\TaskCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin task category management (parent + subcategories, pricing, ordering).
 */
class AdminCategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = TaskCategory::with(['children' => fn ($q) => $q->orderBy('position')])
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();
        return $this->ok('Categories retrieved.', $categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:120',
            'parent_id' => 'nullable|exists:task_categories,id',
            'price'     => 'nullable|numeric|min:0',
            'min_amount'=> 'nullable|integer|min:1',
            'active'    => 'sometimes|boolean',
            'position'  => 'sometimes|integer|min:0',
        ]);

        $category = TaskCategory::create(array_merge([
            'active'   => true,
            'position' => 0,
        ], $validated, ['slug' => \Illuminate\Support\Str::slug($validated['name'])]));

        return $this->ok('Category created.', $category, 201);
    }

    public function update(Request $request, TaskCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:120',
            'parent_id' => 'sometimes|nullable|exists:task_categories,id',
            'price'     => 'sometimes|nullable|numeric|min:0',
            'min_amount'=> 'sometimes|nullable|integer|min:1',
            'active'    => 'sometimes|boolean',
            'position'  => 'sometimes|integer|min:0',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }
        $category->update($validated);
        return $this->ok('Category updated.', $category->fresh());
    }

    public function destroy(TaskCategory $category): JsonResponse
    {
        $category->delete();
        return $this->ok('Category deleted.');
    }
}
