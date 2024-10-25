<?php

namespace App\Http\Controllers;


/**
* @OA\Info(
* title="Swagger with Laravel",
* version="1.0.0",
* )
* @OA\SecurityScheme(
* type="http",
* securityScheme="bearerAuth",
* scheme="bearer",
* bearerFormat="JWT"
* )
*/
abstract class Controller
{
    /**
     * Abstract base controller class.
     *
     * This class serves as a foundation for all controllers in the application.
     * It provides common functionality and structure for controller classes.
     */

    /**
     * Authorize a given action for the current user.
     *
     * @param string $ability The ability to check
     * @param \Illuminate\Database\Eloquent\Collection $arguments Additional arguments
     * @return void
     */

     CONST SERVICE_PROVIDERS = 'providers';
     CONST DEFAULT_WALLET = 'ngn';

    public function authorize($ability, $arguments = [])
    {
        if (!auth()->user()->can($ability)) {
            return get_error_response('Unauthorized action', ['error' => 'Unauthorized action'], 403);
        }
    }
}
