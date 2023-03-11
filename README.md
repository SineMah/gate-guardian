# CloakPort
- [Requirements and limits](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Config](#config)
- [Extending](#extending)

## Requirements

This package proofs an instance of Zitadel User authorizations with JWT.
It needs to track different authorization mechanics:
- Zitadel (PKCE)
- Custom Guards

## Installation
Require SineMah/gate-guardian and add GitHub repository in your `composer.json`.
``` json
{
    "require": {
        "php": "^8.1.0",
        "SineMah/gate-guardian": "dev-main",
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/SineMah/gate-guardian.git"
        }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true
}
```
`composer install`

Publish config if you want to overwrite it. `php artisan vendor:publish --tag=cloak-port-config`

## Usage
You still can extend the behavior with your own `GuardType`. Make sure you implement `GuardTypeContract`.
You are able to add your own Guards if you add a new `GuardType`.

### Define CloakPort in auth config & routes
in your `auth.php` file:
```php
<?php

return [
    // ...
    'guards' => [
        'gate_keeper' => [
            'driver' => 'gate_guardian',
            'provider' => 'users',
        ],
        // ...
    ],
    // ...
];
```

in your `routes/api.php`:
```php
<?php

use App\Http\Controllers\AnyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:gate_keeper'])->group(function () {
  Route::get('/any-route', [AnyController::class, 'index']);
});
```

## Config

### key_identifier
Identify keycloak JWTs if any of the keys `key_identifier` section match in your Bearer token payload.

### guards
Loaded guards. The order direction affects the loading order of your guards.
Keycloak and Passport User Guards will always have the highest priority since they are the strictest.

### factory
Replace `GuardType` with your own factory if needed. Keep in mind you still need coverage for `keycloak`, `passport_user` and `passport_client`.

# Extending
## config
``` php
return [
    // ...
    'guards' => [
        'my_new_guard',
        'zitadel',
        'default'
    ],
    'factory' => My\Package\MyGuardType::class,
];
```

## GuardType

``` php
<?php

namespace My\Package;

use CloakPort\GuardContract;
use CloakPort\GuardTypeContract;
use CloakPort\TokenGuard as DefaultGuard;
use My\Package\TokenGuard as MyGuard;

enum GuardType implements GuardTypeContract
{
    case ZITADEL;
    case MY_GUARD;
    case DEFAULT;

    public static function load(string $backend): self
    {
        return match(strtolower($backend)) {
            'my_guard' => GuardType::MY_GUARD,
            // ...
            default => GuardType::DEFAULT
        };
    }

    public function loadFrom(array $config): GuardContract
    {
        return match ($this) {
            self::MY_GUARD => MyGuard::load($config),
            // ...
            self::DEFAULT => DefaultGuard::load($config)
        };
    }
}
```

### TokenGuard
Overwrite any other public method like `user` if needed.
``` php
<?php

namespace My\Package;

use CloakPort\GuardContract;
use CloakPort\TokenGuard as ParentTokenGuard;
use Illuminate\Contracts\Auth\Guard;

class TokenGuard extends ParentTokenGuard implements Guard, GuardContract
{
    public static function load(array $config): self
    {
        return new self();
    }
    
    public function validate(array $credentials = [])
    {
        // any magic to valid your JWT
        return $this->check();
    }

    public function check()
    {
        return false;
    }
}
```