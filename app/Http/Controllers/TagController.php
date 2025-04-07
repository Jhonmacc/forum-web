<?php
namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    // Exibe a página de criação de tags
    public function index()
    {
        // Busca as tags com os campos necessários (incluindo o 'id')
        $tags = Tag::all(['id', 'code', 'name', 'color', 'icon', 'description']);  // Adicionei 'color' aqui

        // Retorna as tags como um JSON
        return Inertia::render('Tags/Index', [
            'tags' => $tags
        ]);
    }
        public function show() {
            $tags = Tag::all(['id', 'code', 'name', 'color', 'icon', 'description']);

            return response()->json($tags);
        }

    // Salva uma nova tag no banco de dados
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:255|unique:tags',
            'name' => 'required|string|max:255|unique:tags',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ],
        [
            'name.required' => 'O nome da tag é obrigatório.',
            'name.unique' => 'Já existe uma tag com este nome.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#RRGGBB).',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(str_replace(' ', '_', $validated['name']));
        }

        // Se a cor não for fornecida, atribui #000000 (preto) como padrão
        $validated['color'] = $validated['color'] ?? '#000000';

        Tag::create($validated);

        return redirect()->route('tags.index')->with('success', 'Tag criada com sucesso!');
    }
    // Exclui uma tag existente
    public function destroy(Tag $tag)
    {
        // Exclui a tag
        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Tag excluída com sucesso!');
    }
}
