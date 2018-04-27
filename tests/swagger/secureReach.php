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
use App\Http\Controllers\Api\PetsApiController;
use App\Http\Controllers\Api\UsersApiController;

/** @var Router $api */
$api = app(Router::class);

$api->version(config('api.version'), function (Router $api) {
    $api->group(['middleware' => ['bindings']], function (Router $api) {
        ///////////////////
        // Users routes. //
        ///////////////////

        // Returns all users from the system that the user has access to
        $api->get('/users', UsersApiController::class . '@index')->name('users.index');

        //////////////////
        // Pets routes. //
        //////////////////

        // Returns all pets from the system that the user has access to
        $api->get('/pets', PetsApiController::class . '@index')->name('pets.index');
        // Returns a user based on a single ID, if the user does not have access to the pet
        $api->get('/pets/{id}', PetsApiController::class . '@show')->name('pets.show');

        // Routes under auth token security
        $api->group(['middleware' => ['jwt.auth']], function (Router $api) {
            //////////////////
            // Pets routes. //
            //////////////////

            // Creates a new pet in the store.  Duplicates are allowed
            $api->post('/pets', PetsApiController::class . '@store')->name('pets.store');
            // Deletes a single pet based on the ID supplied
            $api->delete('/pets/{id}', PetsApiController::class . '@destroy')->name('pets.destroy');
        });
    });
});
