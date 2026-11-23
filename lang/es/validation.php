<?php

/*
| Mensajes de validación en español (sólo las reglas que usa la API;
| las demás usan el mensaje en inglés de respaldo).
*/

return [
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser un texto.',

    'attributes' => [
        'email' => 'email',
        'password' => 'contraseña',
        'titulo' => 'título',
        'descripcion' => 'descripción',
        'cupo_maximo' => 'cupo máximo',
    ],
];
