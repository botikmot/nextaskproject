<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostMedia;

class PostController extends Controller
{

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
