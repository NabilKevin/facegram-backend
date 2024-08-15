<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Follower id: yang follow
// Following id: yang di follow
class FollowController extends Controller
{
    public function follow(Request $request, $username)
    {
        $user = User::firstWhere('username', $username);
        $me = Auth::user();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if($user->id === $me->id) {
            return response()->json([
                'message' => 'You are not allowed to follow yourself'
            ], 422);
        }

        $follow = Follow::where('follower_id', $me->id)->firstWhere('following_id', $user->id);

        if($follow) {
            return response()->json([
                'message' => 'You are already followed',
                'status' => $follow->is_accepted === 1 ? 'following' : 'requested'
            ], 422);
        }

        $follow = Follow::create([
            'follower_id' => $me->id,
            'following_id' => $user->id,
            'is_accepted' => $user->is_private === 1 ? 0 : 1
        ]);

        return response()->json([
            'message' => 'Follow success',
            'status' => $follow->is_accepted === 1 ? 'following' : 'requested'
        ], 200);

    }

    public function unfollow(Request $request, $username)
    {
        $user = User::firstWhere('username', $username);
        $me = Auth::user();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $follow = Follow::where('follower_id', $me->id)->firstWhere('following_id', $user->id);

        if(!$follow) {
            return response()->json([
                'message' => 'You are not following the user'
            ], 422);
        }

        $follow->delete();

        return response()->json([], 204);

    }

    public function getFollowing()
    {
        $follows = Follow::where('follower_id', Auth::id())->get();
        $following_id = $follows->pluck('following_id');

        $users = User::whereIn('id', $following_id)->get();

        foreach ($users as $user) {
            $user['is_requested'] = $follows->firstWhere('following_id', $user->id)->is_accepted === 1 ? true : false;
        }

        return response()->json([
            'following' => $users
        ], 200);
    }

    public function acceptFollowing(Request $request, $username)
    {
        $user = User::firstWhere('username', $username);
        $me = Auth::user();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $follow = Follow::where('following_id', $me->id)->firstWhere('follower_id', $user->id);

        if(!$follow) {
            return response()->json([
                'message' => 'The user is not following you'
            ], 422);
        }

        if($follow->is_accepted === 1) {
            return response()->json([
                'message' => 'Follow request is already accepted'
            ], 422);
        }

        $follow->update(['is_accepted' => 1]);

        return response()->json([
            'message' => 'Follow request accepted'
        ], 200);
    }
    public function getFollower($username)
    {
        $user = User::firstWhere('username', $username);
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $follows = Follow::where('following_id', $user->id)->get();
        $follower_id = $follows->pluck('follower_id');

        $users = User::whereIn('id', $follower_id)->get();

        foreach ($users as $user) {
            $user['is_requested'] = $follows->firstWhere('follower_id', $user->id)->is_accepted === 1 ? true : false;
        }

        return response()->json([
            'followers' => $users
        ], 200);
    }
}
