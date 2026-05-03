<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'confirmed' => 'A confirmação do campo :attribute não corresponde.',
    'regex' => 'O formato do campo :attribute é inválido.',
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'max' => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'exists' => 'O campo :attribute selecionado é inválido.',
    'array' => 'O campo :attribute deve ser um array.',
    'unique' => 'O campo :attribute já está em uso.',
    'image' => 'O campo :attribute deve ser uma imagem.',
    'mimes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',

    'attributes' => [
        'name' => 'nome',
        'username' => 'nome de usuário',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmação da senha',
        'title' => 'título',
        'description' => 'descrição',
        'content' => 'comentário',
        'body' => 'resposta',
    ],
];
