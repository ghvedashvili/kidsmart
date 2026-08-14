<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        return view('admin.packages.index', [
            'packages' => Package::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Package::create($data);
        return back()->with('success', 'პაკეტი დაემატა');
    }

    public function update(Request $request, Package $package)
    {
        $package->update($this->validated($request));
        return back()->with('success', 'განახლდა');
    }

    public function destroy(Package $package)
    {
        if ($package->subscriptions()->exists()) {
            return back()->with('error', 'პაკეტს აქვს აქტიური გამოწერები — ვერ წაიშლება');
        }
        $package->delete();
        return back()->with('success', 'წაიშალა');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'           => 'required|string|max:60',
            'slug'           => 'required|string|max:30|regex:/^[a-z0-9_-]+$/i',
            'description'    => 'nullable|string|max:500',
            'price_monthly'  => 'required|numeric|min:0',
            'price_yearly'   => 'required|numeric|min:0',
            'max_children'   => 'required|integer|min:0',
            'max_difficulty' => 'required|integer|min:1|max:5',
            'sort_order'     => 'required|integer|min:0',
        ]);

        $data['is_free']   = $request->boolean('is_free');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
