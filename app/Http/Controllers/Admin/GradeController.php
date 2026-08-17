<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        return view('admin.grades.index', ['grades' => Grade::orderBy('number')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|integer|min:1|max:12|unique:grades,number',
            'name'   => 'required|string|max:100',
        ]);
        Grade::create($data);
        return back()->with('success', 'კლასი დაემატა');
    }

    public function toggleActive(Grade $grade)
    {
        $grade->update(['is_active' => ! $grade->is_active]);
        return back()->with('success', $grade->name . ($grade->is_active ? ' გააქტიურდა' : ' გაითიშა'));
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return back()->with('success', 'წაიშალა');
    }
}
