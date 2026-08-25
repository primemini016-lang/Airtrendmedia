<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin currency management. Admin can add currencies and set the USD value
 * (1 USD = x units). Paystack only accepts certain currencies, which are
 * flagged paystack_supported so the system knows which to charge in.
 */
class AdminCurrencyController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->ok('Currencies retrieved.', Currency::orderBy('position')->orderBy('id')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'               => 'required|string|max:6|unique:currencies,code',
            'name'               => 'required|string|max:60',
            'symbol'             => 'required|string|max:6',
            'usd_value'          => 'required|numeric|min:0.000001',
            'is_default'         => 'sometimes|boolean',
            'paystack_supported' => 'sometimes|boolean',
            'active'             => 'sometimes|boolean',
            'position'           => 'sometimes|integer|min:0',
        ]);

        $currency = DB::transaction(function () use ($validated) {
            if (! empty($validated['is_default'])) {
                Currency::where('is_default', true)->update(['is_default' => false]);
            }
            return Currency::create(array_merge([
                'is_default'         => false,
                'paystack_supported' => false,
                'active'             => true,
                'position'           => 0,
            ], $validated));
        });

        return $this->ok('Currency created.', $currency, 201);
    }

    public function update(Request $request, Currency $currency): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'sometimes|string|max:60',
            'symbol'             => 'sometimes|string|max:6',
            'usd_value'          => 'sometimes|numeric|min:0.000001',
            'is_default'         => 'sometimes|boolean',
            'paystack_supported' => 'sometimes|boolean',
            'active'             => 'sometimes|boolean',
            'position'           => 'sometimes|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $currency) {
            if (! empty($validated['is_default'])) {
                Currency::where('is_default', true)->where('id', '!=', $currency->id)->update(['is_default' => false]);
            }
            $currency->update($validated);
        });

        return $this->ok('Currency updated.', $currency->fresh());
    }

    public function destroy(Currency $currency): JsonResponse
    {
        if ($currency->is_default) {
            return $this->error('Cannot delete the default currency.', 400);
        }
        $currency->delete();
        return $this->ok('Currency deleted.');
    }
}
