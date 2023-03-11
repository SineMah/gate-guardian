<?php

namespace GateGuardian;

use GateGuardian\Creator\GuardType;

interface GuardTypeContract
{
    public static function load(string $backend): GuardType;

    public function loadFrom(array $config): GuardContract;
}
