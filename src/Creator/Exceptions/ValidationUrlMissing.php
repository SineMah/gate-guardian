<?php

namespace GateGuardian\Creator\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Sinemah\JsonApi\Error\Error;
use Sinemah\JsonApi\Error\Laravel\Responses\Laravel;

class ValidationUrlMissing extends Exception
{
    protected $message = 'Validation URL was not configured. Add at least \'GATE_GUARDIAN_URL_VALIDATE_JWT\' for JWT validation';

    public function render(): JsonResponse
    {
        return Laravel::response()
            ->add(Error::fromArray(['status' => 500, 'code' => 'e-jwt-20', 'title' => $this->message]))
            ->json();
    }
}
