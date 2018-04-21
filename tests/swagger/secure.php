<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version(config('api.version'), ['namespace' => 'App\Http\Controllers\Api'], function (Router $api) {
    $api->get('/pets', '')->name('');
    $api->get('/pets/{id}', '')->name('');

    // Routes under Auth token security
    $api->group(['middleware' => ['jwt.auth']], function (Router $api) {
        $api->post('/pets', '')->name('');
        $api->delete('/pets/{id}', '')->name('');
    });
});
