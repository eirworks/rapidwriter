<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::stories.index')->name('home');
Route::livewire('/stories/{story}/chapters', 'pages::stories.chapters')->name('chapters');
Route::livewire('/stories/{story}/chapters/{chapter}/editor', 'pages::stories.chapter-editor')->name('chapters.editor');
