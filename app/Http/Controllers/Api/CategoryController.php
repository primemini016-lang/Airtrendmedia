<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\TaskCategory;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * List all active task categories with their children.
     */
    public function index(): JsonResponse
    {
        $categories = TaskCategory::with(['children' => function ($q) {
            $q->where('active', true)->orderBy('position');
        }])
            ->whereNull('parent_id')
            ->where('active', true)
            ->orderBy('position')
            ->get();

        return $this->ok('Categories retrieved.', $categories);
    }
}
