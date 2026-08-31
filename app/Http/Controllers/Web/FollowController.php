<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function toggle(Request $request, User $user)
    {
        $me = auth('web')->user();
        abort_if(!$me || $me->id === $user->id || $user->banned, 422);
        $existing = DB::table('follows')->where('follower_id',$me->id)->where('following_id',$user->id)->first();
        if ($existing) {
            DB::table('follows')->where('id',$existing->id)->delete();
            DB::table('users')->where('id',$me->id)->decrement('following_count');
            DB::table('users')->where('id',$user->id)->decrement('followers_count');
            $following=false;
        } else {
            DB::table('follows')->insert(['follower_id'=>$me->id,'following_id'=>$user->id,'is_accepted'=>true,'created_at'=>now(),'updated_at'=>now()]);
            DB::table('users')->where('id',$me->id)->increment('following_count');
            DB::table('users')->where('id',$user->id)->increment('followers_count');
            $following=true;
            Notification::create(['user_id'=>$user->id,'type'=>'follow','title'=>'New follower','body'=>$me->name.' started following you.','url'=>route('user.public-profile',$me->username)]);
        }
        return response()->json(['following'=>$following,'followers_count'=>max(0,(int)DB::table('users')->where('id',$user->id)->value('followers_count'))]);
    }

    public function list(Request $request, User $user, string $type='followers')
    {
        abort_unless(in_array($type,['followers','following'],true),404);
        $items = $type==='followers' ? $user->followers()->with('country')->paginate(30) : $user->following()->with('country')->paginate(30);
        return view('user.follow-list', compact('user','items','type'));
    }
}
