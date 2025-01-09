<?php
namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Topic;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store(Request $request, Topic $topic)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        // Cria a resposta
        $reply = Reply::create([
            'content' => $request->content,
            'topic_id' => $topic->id,
            'user_id' => auth()->id(),
        ]);

        // Redireciona de volta para o tópico
        return back();
    }
}
