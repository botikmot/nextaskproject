<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;

class TopicController extends Controller
{
    public function index()
    {
        $trendingTopics = Topic::orderBy('mentions', 'desc')->take(5)->get();

        return response()->json($trendingTopics);
    }

    public function show($hashtag)
    {
        $posts = Post::where('content', 'like', "%#$hashtag%")->get();

        //return view('trending.show', compact('hashtag', 'posts'));
    }
}
