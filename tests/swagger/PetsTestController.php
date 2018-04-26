<?php

namespace App\Http\Controllers\Api;

use Pet;

/**
 * Pets test controller.
 */
class PetsTestController extends BaseApiController
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
     * @param integer $id ID of pet to fetch
     */
    public function show(int $id)
    {
        // Do not forget to fill function body
    }

    /**
     * Deletes a single pet based on the ID supplied.
     *
     * @param integer $id ID of pet to delete
     */
    public function destroy(int $id)
    {
        // Do not forget to fill function body
    }
}
