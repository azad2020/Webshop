<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralJsonException extends Exception
{
    protected $code = 422;

    /**
     * Report the exception
     * @return void
     */
    public function report()
    {

    }

    /**
     * Render the exception as an HTTP response
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function render(\Illuminate\Http\Request $request): JsonResponse
    {
        return new JsonResponse([
           'errors' => [
               'message' => $this->getMessage(),
           ]
        ], $this->code);
    }
}
