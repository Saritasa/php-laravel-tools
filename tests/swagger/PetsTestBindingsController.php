<?php

namespace App\Http\Controllers\Api;

use Pet;

/**
 * Pets test bindings controller.
 */
class PetsTestBindingsController extends BaseApiController
{
    /**
     * Resource class that handled by this API controller.
     *
     * @var Pet
     */
    protected $modelsClass = Pet::class;

    /**
     * Returns all pets from the system that the user has access to.
     */
    public function index()
    {
        // Do not forget to fill function body
    }

    /**
     * Creates a new pet in the store.  Duplicates are allowed.
     */
    public function store()
    {
        // Do not forget to fill function body
    }

    /**
     * Returns a user based on a single ID, if the user does not have access to the pet.
     *
     * @param Pet $model Related resource model
     */
    public function show(Pet $model)
    {
        // Do not forget to fill function body
    }

    /**
     * Deletes a single pet based on the ID supplied.
     *
     * @param Pet $model Related resource model
     */
    public function destroy(Pet $model)
    {
        // Do not forget to fill function body
    }
}
