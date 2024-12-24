<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostMedia;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Like;
use Carbon\Carbon;
use App\Notifications\UserNotification;

class PostController extends Controller
{
    public function index()
    {
        // Get the authenticated user's ID
        $user = auth()->user();
        $friends = $user->friends()->pluck('friend_id')->toArray();
        
        $posts = Post::whereIn('user_id', $friends)
                ->orWhere('user_id', $user->id)
                ->with(['user', 'media', 'likes.user', 'comments.user'])
                ->orderBy('created_at', 'desc')
                ->whereNull('deleted_at')
                ->paginate(10);
        
        $posts->getCollection()->transform(function ($post) {
                    $post->link_preview = $post->link_preview; // Add the link preview
                    return $post;
                });
        
        $formattedEvents = $user->getAllEvents();

        return Inertia::render('Social/SocialFeed', [
            'posts' => $posts,
            'events' => $formattedEvents,
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

        preg_match_all('/<span[^>]*data-id="([^"]+)"[^>]*>/i', $request->content, $matches);
    
        if (!empty($matches[1])) {
            // $matches[1] contains the IDs of the mentioned users
            foreach ($matches[1] as $userId) {
                $user = User::find($userId);

                if ($user) {
                    // Create a notification for each mentioned user
                    $user->notify(new UserNotification([
                        'type' => 'mention',
                        'message' => "{$post->user->name} mentioned you in a post.",
                        'user_id' => auth()->id(),  // ID of the user who created the post
                        'post_id' => $post->id,
                    ]));
                }
            }
        }

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

    public function commentPost(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'comment' => 'required', // Adjust validation as needed
        ]);

        // Retrieve the authenticated user (assuming authentication is in place)
        $user = auth()->user(); // or use another method to get the current user

        // Create a new comment associated with the post and user
        $comment = new Comment();
        $comment->content = $request->comment;
        $comment->post_id = $id;  // Associate the comment with the post
        $comment->user_id = $user->id;  // Associate the comment with the authenticated user

        // Save the comment
        $comment->save();

        // Return the newly created comment
        return response()->json(['comment' => $comment->load('user')]);

    }

    public function postCommentDelete($id)
    {
        $comment = Comment::findOrFail($id);

        // Ensure the user is authorized to delete this comment
        if ($comment->user_id !== auth()->id()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
        }

        $comment->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Comment successfully removed',
        ]);
    }

    public function postCommentUpdate(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required',
        ]);

        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->content = $request->comment;
        $comment->save();

        return response()->json(['comment' => $comment->load('user')]);
    }

    public function likePost($id)
    {
        $post = Post::find($id);
        $user = auth()->user();
        
        $like = $post->likes()->where('user_id', $user->id)->first();
        if($like){
            $like->delete();
            $userLiked = false;
        }else{
            $post->likes()->create(['user_id' => $user->id]);
            $userLiked = true;
        }

        return response()->json([
            'success' => true,
            'likes_count' => $post->likes()->count(),
            'user_liked' => $userLiked,
        ]);

    }

    public function getSocialHighlights()
    {
        $user = auth()->user();

        // Fetch the IDs of the user's friends
        $friendIds = $user->friends()->pluck('id');

        // Include the user's own ID
        $userAndFriends = $friendIds->push($user->id);

         // Get the start of the current week (Sunday) and the end (Saturday)
        $startOfWeek = Carbon::now()->startOfWeek(); // Start of this week (Sunday)
        $endOfWeek = Carbon::now()->endOfWeek(); // End of this week (Saturday)

        // Fetch posts from the user and their friends
        $highlights = Post::with('author', 'likes', 'comments')
            ->whereIn('user_id', $userAndFriends)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek]) // Filter posts from this week
            ->withCount(['likes', 'comments']) // Include engagement counts
            ->orderByRaw('(likes_count + comments_count) DESC') // Sort by engagement
            ->take(2) // Limit to top 5 posts
            ->get();

        return response()->json($highlights);
    }



}
