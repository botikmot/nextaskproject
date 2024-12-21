<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostMedia;
use Inertia\Inertia;
use App\Models\User;

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
        
        $posts->getCollection()->transform(function ($post) {
                    $post->link_preview = $post->link_preview; // Add the link preview
                    return $post;
                });

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


    public function searchUsers(Request $request)
    {
        $query = $request->query('query');

        // Get the authenticated user's friends
        $user = auth()->user();
        $friends = $user->friends()->pluck('friend_id'); // Assuming 'friend_id' stores the IDs of friends

        // Search for friends matching the query
        $users = User::whereIn('id', $friends) // Filter by friends
            ->where('id', '!=', $user->id) // Exclude the authenticated user
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', $query . '%') // Match query with username
                ->orWhere('email', 'like', $query . '%'); // Match query with email
            })
            ->limit(5) // Optional: Limit the number of results
            ->get();

        return response()->json($users);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Post successfully removed',
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required',
        ]);

        $post = Post::findOrFail($id);
        $post->content = $request->content;
        $post->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Post successfully updated',
        ]);
    }

}
