<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'caption' => 'required',
            'attachments' => 'required|array',
            'attachments.*' => 'mimes:jpeg,jpg,png,gif,webp'
        ]);

        if($validate->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();

        $post = Post::create([
           'caption' => $data['caption'],
           'user_id' => $request->user()->id
        ]);

        foreach ($data['attachments'] as $image) {
            $imageName = time().'.'.$image->getClientOriginalName();
            $image->move(public_path('posts'), $imageName);
            PostAttachment::create([
                'storage_path' => 'posts/' . $imageName,
                'post_id' => $post->id,
            ]);
        }

        return response()->json([
            'message' => 'Create post success'
        ]);
    }
    public function destroy($id)
    {
        $post = Post::find($id);
        if(!$post){
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }
        if ($post->user_id !== Auth::user()->id) {
            return response()->json([
                'message' => 'Forbidden acess'
            ], 403);
        }

        $post->delete();

        return response()->json([], 204);
    }

    public function index(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'page' => 'min:0|numeric',
            'size' => 'min:1|numeric'
        ]);

        if($validate->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();

        $size = array_key_exists('size', $data) ? $data['size'] : 10;
        $page = array_key_exists('page', $data) ? $data['page'] : 0;

        $posts = Post::with(['user', 'attachments'])->get()->sortBy('created_at');

        return response()->json([
            'page' => $page,
            'size' => $size,
            'posts' => $posts->skip($size * ($page))->take($size)->values()
        ], 200);
    }
}
