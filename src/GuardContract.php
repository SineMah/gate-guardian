<?php

namespace GateGuardian;

interface GuardContract
{
    public static function load(array $config): self;

    public function roles(): array;

    public function hasRole(array|string $roles): bool;

    public function scopes(): array;

    public function claims(): array;

    public function hasScope(string|array $scopes): bool;

    public function name(): string;
}
