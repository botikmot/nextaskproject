<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostMedia;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        // Get the authenticated user's ID
        $user = auth()->user();
        $friends = $user->friends()->pluck('friend_id')->toArray();
        
        $posts = Post::whereIn('user_id', $friends)
                ->orWhere('user_id', $user->id)
                ->with(['user', 'media', 'likes', 'comments.user'])
                ->orderBy('created_at', 'desc')
                ->whereNull('deleted_at')
                ->paginate(10);

        return Inertia::render('Social/SocialFeed', [
            'posts' => $posts,
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,avi,mov|max:20480', // 20MB max
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $media) {
                $path = $media->store('post_media', 'public');
                $mediaType = in_array($media->extension(), ['jpeg', 'png', 'jpg', 'gif', 'svg']) ? 'image' : 'video';

                PostMedia::create([
                    'post_id' => $post->id,
                    'media_path' => $path,
                    'media_type' => $mediaType,
                ]);
            }
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Post created successfully',
        ]);
    }

}
