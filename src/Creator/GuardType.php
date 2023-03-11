<?php

namespace GateGuardian\Creator;

use GateGuardian\GuardContract;
use GateGuardian\GuardTypeContract;
use GateGuardian\TokenGuard as DefaultGuard;
use GateGuardian\Zitadel\TokenGuard as ZitadelGuard;

enum GuardType implements GuardTypeContract
{
    case ZITADEL;
    case DEFAULT;

    public static function load(string $backend): self
    {
        return match(strtolower($backend)) {
            'zitadel' => GuardType::ZITADEL,
            default => GuardType::DEFAULT
        };
    }

    public function loadFrom(array $config): GuardContract
    {
        return match ($this) {
            self::ZITADEL => ZitadelGuard::load($config),
            self::DEFAULT => new DefaultGuard()
        };
    }
}
