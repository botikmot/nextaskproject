<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        $event = Event::create([
            'creator_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'start' => $request->start,
            'end' => $request->end,
            'all_day' => $request->all_day,
            'background_color' => $request->background_color ?? '#40a2e3',
            'location' => $request->location,
        ]);

        // Attach participants if any
        if ($request->has('participants')) {
            $event->participants()->sync($request->participants); // Attach participants
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Event created successfully',
        ]);

    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        $event = Event::findOrFail($id);
        $event->title = $request->title;
        $event->description = $request->description;
        $event->start = $request->start;
        $event->end = $request->end;
        $event->all_day = $request->all_day;
        $event->background_color = $request->background_color;
        $event->location = $request->location;
        $event->save();

        if ($request->has('participants')) {
            $event->participants()->sync($request->participants);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Event successfully updated',
        ]);
    }

}
