<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class UserController extends Controller
{
    use ApiResponse;

    public function dashboard(): JsonResponse
    {
        $user = auth('user')->user();
        $stats = [
            'balance'              => (float) $user->balance,
            'total_earned'         => (float) $user->total_earned,
            'tasks_completed'      => $user->proofs()->where('status', 1)->count(),
            'tasks_pending'        => $user->proofs()->where('status', 0)->count(),
            'tasks_rejected'       => $user->proofs()->where('status', 2)->count(),
            'offers_active'        => $user->tasks()->where('status', 1)->count(),
            'offers_total'         => $user->tasks()->count(),
            'withdrawals_pending'  => $user->withdrawals()->where('status', 0)->count(),
            'referral_count'       => $user->referrals()->count(),
            'referral_activated'   => $user->referrals()->where('is_active', true)->count(),
            'affiliate_earnings'   => (float) $user->affiliateEarnings(),
        ];
        return response()->json($stats);
    }

    public function me(): JsonResponse
    {
        $user = auth('user')->user()->load('country');
        return response()->json([
            'id'           => $user->id,
            'name'         => $user->name,
            'username'     => $user->username,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'country_code' => $user->country_code,
            'country'      => $user->country?->name,
            'image'        => $user->image ? asset($user->image) : null,
            'bio'          => $user->bio,
            'balance'      => (float) $user->balance,
            'total_earned' => (float) $user->total_earned,
            'is_verified'  => (bool) $user->is_verified,
            'is_active'    => (bool) $user->is_active,
            'referral_code'=> $user->referral_code,
            'created_at'   => $user->created_at,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = auth('user')->user();
        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:120',
            'phone'        => 'sometimes|nullable|string|max:30',
            'country_code' => 'sometimes|nullable|string|max:4',
            'bio'          => 'sometimes|nullable|string|max:240',
        ]);
        $user->update($validated);
        return $this->ok('Profile updated successfully.');
    }

    public function updateImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048']);
        $user = auth('user')->user();

        $path = 'uploads/user';
        $file = $request->file('image');
        $filename = $user->id.'_'.uniqid().'.jpg';

        $image = Image::read($file);
        $image->scaleDown(width: 400);
        $image->toJpeg(80)->save(public_path($path.'/'.$filename));

        if ($user->image) {
            $old = public_path($user->image);
            if (file_exists($old)) {
                @unlink($old);
            }
        }

        $user->update(['image' => '/'.$path.'/'.$filename]);
        return $this->ok('Profile image updated.', ['image' => asset($user->image)]);
    }

    public function publicProfile(string $username): JsonResponse
    {
        $user = User::where('username', $username)->first([
            'id', 'name', 'username', 'image', 'bio', 'country_code', 'created_at',
        ]);
        if (! $user) {
            return $this->error('User not found.', 404);
        }
        return response()->json([
            'name'     => $user->name,
            'username' => $user->username,
            'image'    => $user->image ? asset($user->image) : null,
            'bio'      => $user->bio,
            'country'  => $user->country?->name,
            'joined'   => $user->created_at,
            'tasks'    => $user->tasks()->where('status', 1)->count(),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $query = auth('user')->user()->transactions();
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        return response()->json($query->latest()->paginate(15));
    }

    public function bookings(Request $request): JsonResponse
    {
        $query = auth('user')->user()->bookings()->with('task:id,code,title,price,status');
        if ($request->boolean('active')) {
            $query->where('expire_in', '>', now());
        }
        return response()->json($query->latest()->paginate(10));
    }

    public function myTasks(Request $request): JsonResponse
    {
        $query = auth('user')->user()->tasks()->with('category:id,name');
        if (! is_null($status = $request->get('status'))) {
            $query->where('status', (int) $status);
        }
        return response()->json($query->latest()->paginate(10));
    }

    public function deposits(Request $request): JsonResponse
    {
        return response()->json(auth('user')->user()->deposits()->with('method:id,name')->latest()->paginate(10));
    }

    public function withdrawals(Request $request): JsonResponse
    {
        return response()->json(auth('user')->user()->withdrawals()->with('method:id,name')->latest()->paginate(10));
    }

    public function messages(): JsonResponse
    {
        return response()->json(auth('user')->user()->messages()->latest()->get());
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate(['message' => 'required|string|max:1000']);
        auth('user')->user()->messages()->create([
            'message'    => $validated['message'],
            'from_admin' => false,
            'seen'       => false,
        ]);
        return $this->ok('Message sent to support.');
    }

    public function markMessagesSeen(): JsonResponse
    {
        auth('user')->user()->messages()->where('from_admin', true)->where('seen', false)->update(['seen' => true]);
        return $this->ok('Messages marked as seen.');
    }
}
