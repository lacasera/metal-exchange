<?php

use App\Http\Helpers\ApiResponse;

if (! function_exists('api_response')) {
    /**
     * Return a standardized API response.
     */
    function api_response(): ApiResponse
    {
        return new ApiResponse;
    }
}
