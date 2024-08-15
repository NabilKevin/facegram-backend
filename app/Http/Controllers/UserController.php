<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUsers()
    {
        $me = Auth::user();

        $follow = Follow::where('follower_id', $me->id)->get()->pluck('following_id');

        $users = User::whereNotIn('id', $follow)->whereNot('id', $me->id)->get();

        return response()->json(['users' => $users], 200);
    }

    public function getDetail($username)
    {
        $me = Auth::user();
        $user = User::with(['followings', 'followers', 'posts.attachments'])->firstWhere('username', $username);

        $post_count = Count($user->posts);
        $following_count = Count($user->followings);
        $followers_count = Count($user->followers);

        $is_your_account = $user->id === $me->id;

        $following = $user->followers->firstWhere('follower_id', $me->id);
        $following_status = !$following ?  'not-following' : ($following->is_accepted === 1 ? 'following' : 'requested');

        return response()->json([
            'id' => $user->id,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'bio' => $user->bio,
            'is_private' => $user->is_private,
            'created_at' => $user->created_at,
            'is_your_account' => $is_your_account,
            'following_status' => $following_status,
            'post_count' => $post_count,
            'followers_count' => $followers_count,
            'following_count' => $following_count,
            'posts' => $user->posts->map(function ($post) {
                return collect($post)->forget(['user_id', 'updated_at']);
            }),
        ], 200);
    }
}
