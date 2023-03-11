<?php

namespace GateGuardian\Creator\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Sinemah\JsonApi\Error\Error;
use Sinemah\JsonApi\Error\Laravel\Responses\Laravel;

class MissingBearerToken extends Exception
{
    protected $message = 'No valid Bearer token in Authorization header';

    public function render(): JsonResponse
    {
        return Laravel::response()
            ->add(Error::fromArray(['status' => 500, 'code' => 'e-proxy-40', 'title' => $this->message]))
            ->json();
    }
}
