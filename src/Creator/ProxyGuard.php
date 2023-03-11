<?php

namespace GateGuardian\Creator;

use GateGuardian\Creator\Traits\HasMagicCall;
use GateGuardian\GuardContract;

final class ProxyGuard implements ProxyInterface
{
    use HasMagicCall;

    public function __construct(private GuardContract $guard)
    {
    }

    public function getGuard(): GuardContract
    {
        return $this->guard;
    }
}
