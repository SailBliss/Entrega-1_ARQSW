<?php

return [
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'email' => 'El campo :attribute debe contener una dirección de correo válida.',
    'in' => 'La opción seleccionada para :attribute no es válida.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser mayor o igual a :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El campo :attribute ya está en uso.',
    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'is_admin' => 'rol de administrador',
        'brand' => 'marca',
        'description' => 'descripción',
        'price' => 'precio',
        'stock' => 'stock',
        'image' => 'imagen',
        'quantity' => 'cantidad',
        'value' => 'preferencia',
    ],
];
