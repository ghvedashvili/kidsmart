<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\TopicVideo;
use Illuminate\Http\Request;

class TopicVideoController extends Controller
{
    public function store(Request $request, Topic $topic)
    {
        $data = $request->validate([
            'youtube_url' => 'required|string|max:200',
            'title'       => 'nullable|string|max:200',
        ]);

        $id = TopicVideo::extractId($data['youtube_url']);

        if (! $id) {
            return back()->withErrors(['youtube_url' => 'YouTube URL ან ID არასწორია'])->withInput();
        }

        $topic->videos()->create([
            'youtube_id' => $id,
            'title'      => $data['title'] ?: null,
        ]);

        return back()->with('success', 'ვიდეო დაემატა');
    }

    public function destroy(TopicVideo $video)
    {
        $video->delete();
        return back()->with('success', 'ვიდეო წაიშალა');
    }
}
