# Installing Spipu User Bundle

[back](./README.md)

## Requirements

- PHP >= 8.3
- Symfony >= 7.4
- `spipu/core-bundle`
- `spipu/ui-bundle`
- `spipu/configuration-bundle`

## Installation

```bash
composer require spipu/user-bundle
```

## Configuration

### 1. Register the bundle

In `config/bundles.php`:

```php
return [
    // ...
    Spipu\CoreBundle\SpipuCoreBundle::class => ['all' => true],
    Spipu\UiBundle\SpipuUiBundle::class => ['all' => true],
    Spipu\ConfigurationBundle\SpipuConfigurationBundle::class => ['all' => true],
    Spipu\UserBundle\SpipuUserBundle::class => ['all' => true],
];
```

### 2. Create your User entity

The bundle provides `AbstractUser` as a mapped superclass. Create a concrete entity in your application:

```php
// src/App/Entity/User.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Spipu\UserBundle\Entity\AbstractUser;

#[ORM\Entity(repositoryClass: 'Spipu\UserBundle\Repository\UserRepository')]
#[ORM\Table(name: 'spipu_user')]
class User extends AbstractUser
{
    // Add any application-specific fields here
}
```

### 3. Wire the ModuleConfiguration service

The bundle requires a `ModuleConfigurationInterface` implementation wired in your `config/services.yaml`. The built-in `ModuleConfiguration` class accepts the following constructor arguments:

| Argument | Type | Description |
|----------|------|-------------|
| `$entityName` | string | Short entity name used for Doctrine queries (e.g. `'App\Entity\User'`) |
| `$entityClassName` | string | FQCN of the user entity (e.g. `'App\Entity\User'`) |
| `$allowAccountCreation` | bool | Whether self-registration is enabled |
| `$allowPasswordRecovery` | bool | Whether password recovery is enabled |
| `$allowRememberMe` | bool | Whether the "Remember me" checkbox is displayed on the login form |

Example wiring:

```yaml
# config/services.yaml
Spipu\UserBundle\Service\ModuleConfigurationInterface:
    class: Spipu\UserBundle\Service\ModuleConfiguration
    public: true
    arguments:
        - '\App\Entity\User'   # entityName
        - '\App\Entity\User'   # entityClassName
        - '%env(bool:APP_ACCOUNT_CREATION)%'   # allowAccountCreation
        - '%env(bool:APP_ACCOUNT_RECOVERY)%'   # allowPasswordRecovery
        - '%env(bool:APP_REMEMBER_ME)%'        # allowRememberMe
```

To use a custom configuration class, implement `Spipu\UserBundle\Service\ModuleConfigurationInterface` and bind it instead.

### 4. Configure Symfony Security

In `config/packages/security.yaml`:

```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
        spipu_users:
            entity:
                class: App\Entity\User
                property: username

    firewalls:
        main:
            pattern: ^/
            provider: spipu_users
            user_checker: Spipu\UserBundle\Security\UserChecker
            form_login:
                login_path: spipu_user_security_login
                check_path: spipu_user_security_login
                enable_csrf: true
                default_target_path: app_home
                remember_me: true
            remember_me:
                secret: '%kernel.secret%'
                name: myapp_main_remember # <app>_<firewall>_remember
                lifetime: 2592000 # 30 days - to adapt to your application
                httponly: true
                secure: auto
                signature_properties: ['password', 'email']
            logout:
                path: spipu_user_security_logout
                target: app_home

    access_control:
        - { path: ^/login,  roles: PUBLIC_ACCESS }
        - { path: ^/logout, roles: PUBLIC_ACCESS }
```

Key points:
- `user_checker: Spipu\UserBundle\Security\UserChecker` — required; blocks login for inactive users and users without a password
- `remember_me` — must be consistent with `ModuleConfiguration::$allowRememberMe`. Remove these options from the firewall if the feature is disabled:
  - `name` — must be **unique per firewall**. When several firewalls share the same cookie name on the same path, each firewall tries to authenticate with the cookie of the other one, fails, and clears it.
  - `lifetime` — 30 days is only an example: each application must choose its own value according to its security needs (e.g. 1 day for a sensitive application).
  - `signature_properties` — all existing remember me cookies of a user become invalid when their password or email changes. Note that changing this list (or `secret`) invalidates **all** existing cookies: every user has to log in again once.
  - Disabling the feature, or invalidating the cookies, does not close the sessions already opened: they remain valid until they expire.
- Route names are `spipu_user_security_login` and `spipu_user_security_logout` (not `spipu_user_login`/`spipu_user_logout`)

**Incident procedure** (e.g. remember me cookies stolen), to stop all the remember me authentications:
1. Set `APP_REMEMBER_ME` to `false`: the checkbox is hidden and no new cookie is created.
2. Remove the `remember_me` options from the firewall and redeploy: the existing cookies are ignored.
3. Purge the sessions already opened (e.g. flush the Redis session storage): users have to log in again.

Do not rotate `kernel.secret` to invalidate the cookies: it also invalidates the CSRF tokens, the signed URLs and the account activation / recovery tokens.

### 5. Import routes

In `config/routes.yaml`:

```yaml
spipu_user:
    resource: '@SpipuUserBundle/config/routes.yaml'
```

All routes are registered via PHP attributes on the controllers. The routes resource uses `type: attribute` with prefix `/`.

### 6. Import ConfigurationBundle keys

The UserBundle ships a `spipu_configuration.yaml` file with its security settings. Import it in `config/packages/spipu_configuration.yaml`:

```yaml
imports:
    - { resource: "@SpipuUserBundle/config/spipu_configuration.yaml" }
```

### 7. (Optional) Override the sender email

The bundle's `MailConfiguration` defaults to `no-reply@mysite.fr` as the sender. Override this by implementing `MailConfigurationInterface`:

```php
// src/App/Service/MyMailConfiguration.php
namespace App\Service;

use Spipu\UserBundle\Service\MailConfigurationInterface;

class MyMailConfiguration implements MailConfigurationInterface
{
    public function getEmailFrom(): string
    {
        return 'no-reply@yoursite.com';
    }
}
```

Then bind it in `services.yaml`:

```yaml
Spipu\UserBundle\Service\MailConfigurationInterface:
    class: App\Service\MyMailConfiguration
```

### 8. Run migrations

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 9. (Optional) Load the first admin user fixture

The bundle ships a `FirstUserFixture` (fixture code: `first-user`) that creates an initial `admin` user with role `ROLE_SUPER_ADMIN`. Load it via CoreBundle's fixture command:

```bash
php bin/console spipu:fixtures:load
```

Default credentials created by the fixture:
- username: `admin`
- email: `admin@admin.fr`
- password: `password`

> Change the password immediately after loading in a real environment.

## Admin UI

The admin user management interface is available at `/user/`.
It requires role `ROLE_ADMIN_MANAGE_USER_SHOW`. See [Roles Reference](./roles.md).

[back](./README.md)
