<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DepositMethod;
use App\Models\WithdrawalMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin payment-method management: deposit methods and withdrawal methods
 * (including gift-card options).
 */
class AdminMethodController extends Controller
{
    use ApiResponse;

    /* ---------- Deposit methods ---------- */

    public function depositMethods(): JsonResponse
    {
        return $this->ok('Deposit methods retrieved.', DepositMethod::orderBy('position')->get());
    }

    public function storeDepositMethod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'slug'        => 'required|string|max:60|unique:deposit_methods,slug',
            'logo'        => 'nullable|string|max:255',
            'min_amount'  => 'nullable|numeric|min:0',
            'instructions'=> 'nullable|string|max:5000',
            'active'      => 'sometimes|boolean',
            'manual'      => 'sometimes|boolean',
            'position'    => 'sometimes|integer|min:0',
        ]);
        return $this->ok('Deposit method created.', DepositMethod::create(array_merge(['active'=>true,'manual'=>false,'position'=>0], $validated)), 201);
    }

    public function updateDepositMethod(Request $request, DepositMethod $method): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:120',
            'slug'        => 'sometimes|string|max:60|unique:deposit_methods,slug,'.$method->id,
            'logo'        => 'sometimes|nullable|string|max:255',
            'min_amount'  => 'sometimes|nullable|numeric|min:0',
            'instructions'=> 'sometimes|nullable|string|max:5000',
            'active'      => 'sometimes|boolean',
            'manual'      => 'sometimes|boolean',
            'position'    => 'sometimes|integer|min:0',
        ]);
        $method->update($validated);
        return $this->ok('Deposit method updated.', $method->fresh());
    }

    public function destroyDepositMethod(DepositMethod $method): JsonResponse
    {
        $method->delete();
        return $this->ok('Deposit method deleted.');
    }

    /* ---------- Withdrawal methods ---------- */

    public function withdrawalMethods(): JsonResponse
    {
        return $this->ok('Withdrawal methods retrieved.',
            WithdrawalMethod::with('options')->whereNull('parent_id')->orderBy('position')->get());
    }

    public function storeWithdrawalMethod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:120',
            'slug'       => 'required|string|max:60|unique:withdrawal_methods,slug',
            'logo'       => 'nullable|string|max:255',
            'min_amount' => 'nullable|numeric|min:0',
            'active'     => 'sometimes|boolean',
            'gift_card'  => 'sometimes|boolean',
            'position'   => 'sometimes|integer|min:0',
            'parent_id'  => 'nullable|exists:withdrawal_methods,id',
        ]);
        return $this->ok('Withdrawal method created.', WithdrawalMethod::create(array_merge(['active'=>true,'gift_card'=>false,'position'=>0], $validated)), 201);
    }

    public function updateWithdrawalMethod(Request $request, WithdrawalMethod $method): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'sometimes|string|max:120',
            'slug'       => 'sometimes|string|max:60|unique:withdrawal_methods,slug,'.$method->id,
            'logo'       => 'sometimes|nullable|string|max:255',
            'min_amount' => 'sometimes|nullable|numeric|min:0',
            'active'     => 'sometimes|boolean',
            'gift_card'  => 'sometimes|boolean',
            'position'   => 'sometimes|integer|min:0',
            'parent_id'  => 'sometimes|nullable|exists:withdrawal_methods,id',
        ]);
        $method->update($validated);
        return $this->ok('Withdrawal method updated.', $method->fresh());
    }

    public function destroyWithdrawalMethod(WithdrawalMethod $method): JsonResponse
    {
        $method->delete();
        return $this->ok('Withdrawal method deleted.');
    }
}
