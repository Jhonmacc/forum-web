<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
        public function search(Request $request)
    {
        $query = $request->query('query');
        $users = User::where('username', 'like', "%{$query}%")->get(['id', 'username']);
        return response()->json($users);
    }
}
