<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::counter');
Route::livewire('/post/create', 'pages::post.create');