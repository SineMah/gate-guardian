<?php

namespace GateGuardian\Creator\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Sinemah\JsonApi\Error\Error;
use Sinemah\JsonApi\Error\Laravel\Responses\Laravel;

class ExpiredBearerToken extends Exception
{
    protected $message = 'Token is Expired';

    public function render(): JsonResponse
    {
        return Laravel::response()
            ->add(Error::fromArray(['status' => 401, 'code' => 'e-jwt-30', 'title' => $this->message]))
            ->json();
    }
}
