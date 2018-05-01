<?php

namespace App\Http\Controllers\Api;

use User;

/**
 * Users test bindings controller.
 */
class UsersTestBindingsController extends BaseApiController
{
    /**
     * Resource class that handled by this API controller.
     *
     * @var User
     */
    protected $modelsClass = User::class;

    /**
     * Returns all users from the system that the user has access to.
     */
    public function index()
    {
        // Do not forget to fill function body
    }
}
