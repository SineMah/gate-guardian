<?php

namespace GateGuardian\Creator;

use GateGuardian\GuardContract;

interface ProxyInterface
{
    public function getGuard(): GuardContract;
}
