<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be a string.',
    'email' => 'The :attribute field must be a valid email address.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'regex' => 'The :attribute field format is invalid.',
    'min' => [
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'max' => [
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'exists' => 'The selected :attribute is invalid.',
    'array' => 'The :attribute field must be an array.',
    'unique' => 'The :attribute has already been taken.',
    'image' => 'The :attribute must be an image.',
    'mimes' => 'The :attribute must be a file of type: :values.',

    'attributes' => [
        'name' => 'name',
        'username' => 'username',
        'email' => 'email',
        'password' => 'password',
        'password_confirmation' => 'password confirmation',
        'title' => 'title',
        'description' => 'description',
        'content' => 'comment',
        'body' => 'reply',
    ],
];
