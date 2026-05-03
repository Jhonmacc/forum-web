<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'forum_name' => 'required|string|max:50',
        ]);

        Setting::set('forum_name', $validated['forum_name']);

        return back()->with('success', __('messages.settings_updated'));
    }
}
