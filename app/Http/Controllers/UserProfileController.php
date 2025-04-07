<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function show($id)
    {
        $user = User::with(['posts' => function ($query) {
            $query->with(['tags', 'user'])
                  ->withCount(['likes', 'comments'])
                  ->orderBy('created_at', 'desc'); 
        }])->findOrFail($id);

        return Inertia::render('Forum/UserProfile', [
            'userId' => $user->id,
            'user' => $user,
            'posts' => $user->posts,
        ]);
    }

    public function getUserByUsername($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        return response()->json(['id' => $user->id]);
    }
}
