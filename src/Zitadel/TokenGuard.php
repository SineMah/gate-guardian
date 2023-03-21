<?php

namespace GateGuardian\Zitadel;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GateGuardian\Creator\Exceptions\ExpiredBearerToken;
use GateGuardian\GuardContract;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Auth\Authenticatable as Authenticatable;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;
use Throwable;
use UnexpectedValueException;

class TokenGuard implements Guard, GuardContract
{
    protected ?array $decodedToken = null;
    protected string $uuid = 'dfe95955-804d-491e-82fa-00756b087a66';

    protected string $jwkCacheKey = 'jwk.zitadel';
    protected ?string $authorizationCacheKey = null;
    protected int $validationMaxTries = 3;
    protected int $validationTries = 0;

    public static function load(array $config): self
    {
        return new self();
    }

    public function user()
    {
        return null;
    }

    /**
     * @throws ExpiredBearerToken
     */
    public function validate(array $credentials = [])
    {
        if($this->validateToken()) {

            $this->loadToken();
        }

        return $this->decodedToken !== null;
    }

    public function id()
    {
        return null;
    }

    public function check()
    {
        return $this->decodedToken !== null;
    }

    public function client()
    {
        return null;
    }

    public function guest(): bool
    {
        return false;
    }

    public function setUser(Authenticatable $user): self
    {
        return $this;
    }

    public function hasUser(): bool
    {
        return false;
    }

    public function roles(): array
    {
        $roles = [];
        $keyValue = array_values(Arr::only($this->decodedToken, config('gate_guardian.key_identifier')));

        foreach ($keyValue as $role) {
            $roles = array_merge($roles, array_keys($role));
        }

        return array_unique($roles);
    }

    public function hasRole(array|string $roles): bool
    {
        return count(
                array_intersect(
                    $this->roles(),
                    is_string($roles) ? [$roles] : $roles
                )
            ) > 0;
    }

    public function scopes(): array
    {
        return [];
    }

    public function hasScope(string|array $scopes): bool
    {
        return count(
                array_intersect(
                    $this->scopes(),
                    is_string($scopes) ? [$scopes] : $scopes
                )
            ) > 0;
    }

    public function claims(): array
    {
        return $this->decodedToken;
    }

    public function name(): string
    {
        return 'zitadel';
    }

    /**
     * @throws ExpiredBearerToken
     */
    protected function validateToken(): bool
    {
        $this->authorizationCacheKey = sprintf(
            'jwt.%s',
            Uuid::uuid5($this->uuid, request()->header('Authorization'))
        );

        return $this->validateTokenActive() && $this->validateWithJwk();
    }

    protected function validateTokenActive(): bool
    {
        return cache()->remember($this->authorizationCacheKey, config('gate_guardian.cache_ttl'), function () {
            $response = Http::withHeaders(
                ['Authorization' => request()->header('Authorization')]
            )->get(config('gate_guardian.validate_jwt_url'));

            return $response->status() === 200 && $response->json('locale') !== null;
        });
    }

    /**
     * @throws ExpiredBearerToken
     */
    protected function validateWithJwk(): bool
    {
        if($keys = $this->loadJwk()) {

            $this->validationTries++;

            try {
                JWT::decode(str_replace('Bearer ', '', request()->header('Authorization')), JWK::parseKeySet($keys));
            }catch(ExpiredException $e) {

                throw new ExpiredBearerToken();
            }catch(UnexpectedValueException $e) {

                cache()->forget($this->jwkCacheKey);

                return $this->validateWithJwk();
            }catch(Throwable $e) {

                return false;
            }

            return $this->validationTries <= $this->validationMaxTries;
        }

        return true;
    }

    protected function loadToken(): void
    {
        $accessToken = str_replace('Bearer ', '', request()->header('Authorization'));
        $tks = explode('.', $accessToken);
        [$headb64, $bodyb64, $cryptob64] = $tks;

        $this->decodedToken = json_decode(JWT::urlsafeB64Decode($bodyb64), true);
    }

    protected function loadJwk(): mixed
    {
        if($jwkUrl = config('gate_guardian.jwk_uri')) {

            return cache()->remember($this->jwkCacheKey, config('gate_guardian.cache_ttl'), fn () =>
                Http::get($jwkUrl)->json()
            );
        }

        return null;
    }
}
