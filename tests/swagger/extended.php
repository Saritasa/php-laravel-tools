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

/** @var Router $api */
$api = app(Router::class);

$api->version(config('api.version'), function (Router $api) {
    $api->group(['middleware' => ['bindings']], function (Router $api) {
        //////////////////
        // Pets routes. //
        //////////////////

        $api->get('/pets', PetsApiController::class . '@index')->name('pets.index');
        $api->post('/pets', PetsApiController::class . '@store')->name('pets.store');
        $api->get('/pets/{id}', PetsApiController::class . '@show')->name('pets.show');
        $api->delete('/pets/{id}', PetsApiController::class . '@destroy')->name('pets.destroy');
    });
});
