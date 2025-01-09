<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Topic;
use App\Models\Category;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function show(Category $category)
    {
        $topics = $category->topics()->with('user')->get(); // Aqui estamos buscando os tópicos da categoria e incluindo os usuários
        return Inertia::render('Forum/Category', [
            'category' => $category,
            'topics' => $topics,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $topic = Topic::create([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
        ]);

        // Redireciona para a categoria do tópico criado
        return redirect()->route('forum.category', ['category' => $topic->category_id]);
    }
}
