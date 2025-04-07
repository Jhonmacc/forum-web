<?php
namespace App\Http\Controllers;

use App\Models\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function show(Reply $reply)
    {
        $reply->load(['user', 'children.user', 'children.likes']);
        return response()->json($reply, 200);
    }
}
