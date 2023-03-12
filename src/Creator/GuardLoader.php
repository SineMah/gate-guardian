<?php

namespace GateGuardian\Creator;

use GateGuardian\Creator\Exceptions\MissingBearerToken;
use GateGuardian\Creator\Traits\HasMagicCall;
use GateGuardian\GuardContract;
use GateGuardian\GuardTypeContract;

class GuardLoader
{
    use HasMagicCall;

    private static array $loaded = [];

    /**
     * @throws MissingBearerToken
     */
    public static function load(array $config): ProxyGuard
    {
        $guardName = count(array_intersect(config('gate_guardian')['key_identifier'], array_keys(self::tokenPayload()))) > 0 ? 'zitadel' : 'default';
        $guard = GuardType::load($guardName)->loadFrom($config);

        self::$loaded[] = $guard->name();

        if($guard->validate(['request' => request()]) === false) {
            $guard = self::reload($config);
        }

        return new ProxyGuard($guard);
    }

    public static function reload(array $config): ?GuardContract
    {
        $validGuard = null;
        $gardTypeClass = config('gate_guardian')['factory'];

        foreach (config('gate_guardian')['guards'] as $guardType) {

            if(in_array($guardType, self::$loaded) === false) {
                $guardTypeInstance = $gardTypeClass::load($guardType);

                assert($guardTypeInstance instanceof GuardTypeContract);

                $guard = $guardTypeInstance->loadFrom($config);

                self::$loaded[] = $guard->name();

                if($guard->validate()) {
                    $validGuard = $guard;
                    break;
                }
            }
        }

        return $validGuard;
    }

    /**
     * @throws MissingBearerToken
     */
    private static function tokenPayload(): array
    {
        [$header, $payload, $signature] = explode('.', str_replace('Bearer', '', request()->header('Authorization', '..')));

        if($payload) {
            return json_decode(base64_decode($payload), true);
        }

        throw new MissingBearerToken();
    }
}
