<?php
Route::get('/', function () {
return view('welcome');
});
Route::get('/botman/tinker', 'BotManController@tinker');
Route::match(['get', 'post'], '/hook', 'BotManController@handle');
