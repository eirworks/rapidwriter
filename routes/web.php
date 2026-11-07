<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::stories.index')->name('home');
Route::livewire('/stories/{story}/chapters', 'pages::stories.chapters')->name('chapters');
