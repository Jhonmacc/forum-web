<?php
namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    // Exibe a página de criação de tags
    public function index()
    {
        // Busca as tags com os campos necessários (incluindo o 'id')
        $tags = Tag::query()
            ->select(['id', 'code', 'name', 'color', 'icon', 'description'])
            ->withCount('posts')
            ->orderBy('name')
            ->get();

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
        $this->mergeGeneratedCode($request);

        $validated = $request->validate([
            'code' => 'nullable|string|max:255|unique:tags',
            'name' => 'required|string|max:255|unique:tags',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ],
        [
            'name.required' => __('messages.tag_name_required'),
            'name.unique' => __('messages.tag_name_unique'),
            'color.regex' => __('messages.tag_color_format'),
        ]);

        $validated = $this->normalizeTagData($validated);

        Tag::create($validated);

        return redirect()->back()->with('success', __('messages.tag_created'));
    }

    public function update(Request $request, Tag $tag)
    {
        $this->mergeGeneratedCode($request);

        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tags', 'code')->ignore($tag->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($tag->id),
            ],
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ],
        [
            'name.required' => __('messages.tag_name_required'),
            'name.unique' => __('messages.tag_name_unique'),
            'color.regex' => __('messages.tag_color_format'),
        ]);

        $tag->update($this->normalizeTagData($validated));

        return redirect()->back()->with('success', __('messages.tag_updated'));
    }

    // Exclui uma tag existente
    public function destroy(Tag $tag)
    {
        // Exclui a tag
        $tag->delete();

        return redirect()->back()->with('success', __('messages.tag_deleted'));
    }

    private function normalizeTagData(array $data): array
    {
        $data['color'] = $data['color'] ?? '#F5B800';
        $data['icon'] = $data['icon'] ?? 'fa-solid fa-tag';

        return $data;
    }

    private function mergeGeneratedCode(Request $request): void
    {
        if ($request->filled('code') || !$request->filled('name')) {
            return;
        }

        $code = str((string) $request->input('name'))
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '_')
            ->trim('_')
            ->upper()
            ->toString();

        $request->merge(['code' => $code ?: 'NOVA_TAG']);
    }
}
