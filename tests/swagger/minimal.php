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
    // Public routes without auth security
    $api->group(['middleware' => ['bindings']], function (Router $api) {
        //////////////////
        // Pets routes. //
        //////////////////

        $api->get('/pets', 'PetsApiController@index')->name('pets.index');
    });
});
