<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;

class WordController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'path_image' => 'required|file|image',
            'path_document' => 'required|file|mimes:doc,docx'
        ]);

        $imagePath = $request->file('path_image')->store('images', 'public');
        $documentPath = $request->file('path_document')->store('documents', 'public');

        Word::create(array_merge($validated, [
            'path_image' => $imagePath,
            'path_document' => $documentPath
        ]));

        return response()->json(['message' => 'Word saved successfully!']);
    }

    public function index()
    {
        return Word::all();
    }
    public function show($id)
    {
        $word = Word::findOrFail($id);
        $documentPath = storage_path('app/public/' . $word->path_document);

        // Carrega o documento Word
        $phpWord = IOFactory::load($documentPath);

        // Configura o Writer para HTML
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        // Extrai o conteúdo em HTML
        ob_start();
        $htmlWriter->save('php://output');
        $content = ob_get_clean();

        // Remove a formatação de "text-align: justify" (Ctrl+J do Word)
        // Usamos preg_replace para garantir que os parágrafos não tenham alinhamento justificado
        $content = preg_replace('/<p[^>]*style="[^"]*text-align:\s*justify[^"]*"[^>]*>/i', '<p>', $content);

        // Remover também qualquer inline "text-align: justify"
        $content = preg_replace('/text-align:\s*justify;/i', '', $content);

        // Retorna a view utilizando Inertia
        return Inertia::render('Document/VisualWord', [
            'word' => $word,
            'content' => $content, // Envia o conteúdo para a view
        ]);
    }

}
