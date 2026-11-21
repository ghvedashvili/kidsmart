<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNote;
use Illuminate\Http\Request;

/** „ჩანაწერები“ პანელი: შენახვა და გაშლილი/ჩაკეცილი მდგომარეობის დამახსოვრება (მხოლოდ ადმინი) */
class AdminNoteController extends Controller
{
    public function save(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $data = $request->validate(['body' => 'nullable|string|max:200000']);
        $note = AdminNote::for(auth()->id());
        $note->body = (string) ($data['body'] ?? '');
        $note->save();
        return response()->json(['ok' => true, 'saved_at' => $note->updated_at?->format('H:i')]);
    }

    public function toggle(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $note = AdminNote::for(auth()->id());
        $note->is_open = $request->boolean('open');
        $note->save();
        return response()->json(['ok' => true, 'open' => $note->is_open]);
    }
}
