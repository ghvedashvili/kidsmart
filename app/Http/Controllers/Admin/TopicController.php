<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index()
    {
        return view('admin.topics.index', [
            'topics' => Topic::with('grade')->withCount('questionTemplates')->orderBy('grade_id')->get(),
            'grades' => Grade::orderBy('number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'name'     => 'required|string|max:100',
        ]);
        Topic::create($data);
        return back()->with('success', 'თოპიქი დაემატა');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();
        return back()->with('success', 'წაიშალა');
    }
}
