<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin FAQ management.
 */
class AdminFaqController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->ok('FAQs retrieved.', Faq::orderBy('position')->latest()->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string|max:5000',
            'position' => 'sometimes|integer|min:0',
        ]);
        return $this->ok('FAQ created.', Faq::create($validated), 201);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'sometimes|string|max:500',
            'answer'   => 'sometimes|string|max:5000',
            'position' => 'sometimes|integer|min:0',
        ]);
        $faq->update($validated);
        return $this->ok('FAQ updated.', $faq->fresh());
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();
        return $this->ok('FAQ deleted.');
    }
}
