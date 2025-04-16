<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    // ... other handler code

    /**
     * Convert an authentication exception into a response.
     */

     use ApiResponseTrait;


    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->unauthenticatedResponse();
            }
        });
    }
}
