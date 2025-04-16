<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function unauthenticatedResponse()
    {
        return response()->json([
            'message' => 'Unauthenticated',
            'status' => 401
        ], 401);
    }
}
