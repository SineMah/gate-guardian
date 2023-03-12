<?php

namespace GateGuardian;

interface GuardContract
{
    public static function load(array $config): self;

    public function roles(): array;

    public function hasRole(string $role): bool;

    public function scopes(): array;

    public function hasScope(string|array $scope): bool;

    public function name(): string;
}
