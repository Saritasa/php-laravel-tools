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
    /////////////////////////////
    // Pets management routes. //
    /////////////////////////////

    // Returns all pets from the system that the user has access to
    $api->get('/pets', '')->name('');
    // Returns a user based on a single ID, if the user does not have access to the pet
    $api->get('/pets/{id}', '')->name('');

    // Routes under Auth token security
    $api->group(['middleware' => ['jwt.auth']], function (Router $api) {
        /////////////////////////////
        // Pets management routes. //
        /////////////////////////////

        // Creates a new pet in the store.  Duplicates are allowed
        $api->post('/pets', '')->name('');
        // Deletes a single pet based on the ID supplied
        $api->delete('/pets/{id}', '')->name('');
    });
});
