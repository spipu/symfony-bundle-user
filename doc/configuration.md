# User Bundle Configuration

[back](./README.md)

## Module Configuration

The UserBundle's behavior is controlled entirely through the `ModuleConfigurationInterface` service, which is wired in your application's `services.yaml`. There are no ConfigurationBundle database keys for this bundle.

The built-in `ModuleConfiguration` class exposes:

| Method | Description |
|--------|-------------|
| `getEntityName(): string` | Short name / FQCN used for Doctrine queries |
| `getEntityClassName(): string` | FQCN of the concrete user entity |
| `hasAllowAccountCreation(): bool` | Whether the self-registration flow is enabled |
| `hasAllowPasswordRecovery(): bool` | Whether the password recovery flow is enabled |
| `getNewEntity(): UserInterface` | Instantiates a new entity of the configured class |

When `allowAccountCreation` is `false`, the `/account/create` route returns a 404. When `allowPasswordRecovery` is `false`, the `/account/recovery` route returns a 404.

See [Installation](./install.md#3-wire-the-moduleconfiguration-service) for how to wire this service.

## Email Configuration

The UserBundle sends two emails, both using the `MailManager` service:

| Method | Triggered by | Template |
|--------|-------------|----------|
| `sendActivationEmail(UserInterface $user)` | Self-registration | `@SpipuUser/email/confirm.html.twig` |
| `sendRecoveryEmail(UserInterface $user)` | Password recovery request; admin-initiated reset | `@SpipuUser/email/recover.html.twig` |

The sender address comes from `MailConfigurationInterface::getEmailFrom()`. The default implementation (`MailConfiguration`) returns `no-reply@mysite.fr`. Override it by implementing the interface — see [Installation](./install.md#6-optional-override-the-sender-email).

### Overriding email templates

Copy the default templates into your application's `templates/bundles/SpipuUserBundle/email/` directory:

| Template path | Description |
|---------------|-------------|
| `email/confirm.html.twig` | Account activation email (contains the confirmation link) |
| `email/recover.html.twig` | Password recovery email (contains the new-password link) |

Both templates receive:
- `user` — the `UserInterface` entity
- `confirmLink` — the absolute URL for the action

## Token Management

Account activation and password recovery both use `UserTokenManager`, which generates and validates HMAC tokens:

- `generate(UserInterface $user): string` — sets `tokenDate` on the user, persists, returns a SHA-256 token
- `isValid(UserInterface $user, string $token): bool` — validates the token
- `reset(UserInterface $user): void` — clears `tokenDate` on the user

The token is derived from: user id, email, username, `createdAt`, `tokenDate`, and `kernel.secret`. There is no time-based expiry — the token is valid until it is consumed (reset) or the user's `tokenDate` is cleared.

## Events

The bundle dispatches `Spipu\UserBundle\Event\UserEvent` for the following user actions. The event name follows the pattern `spipu.user.action.<action>`.

| Event name | Action constant | Triggered when |
|------------|----------------|---------------|
| `spipu.user.action.create` | `create` | Self-registration form submitted |
| `spipu.user.action.confirm` | `confirm` | Account activation link clicked and token validated |
| `spipu.user.action.recovery_asked` | `recovery_asked` | Password recovery form submitted |
| `spipu.user.action.recovery_allow` | `recovery_allow` | Recovery link clicked and token validated (before new password form) |
| `spipu.user.action.recovery_update` | `recovery_update` | New password saved via recovery link |
| `spipu.user.action.edit` | `edit` | User edits their own profile (name/email) |
| `spipu.user.action.password` | `password` | User changes their own password from profile |

`UserEvent` provides:
- `getUser(): UserInterface` — the affected user
- `getAction(): string` — the action string (e.g. `'create'`)
- `getEventCode(): string` — full event name (`spipu.user.action.<action>`)

Subscribe to these events with a standard Symfony event subscriber:

```php
use Spipu\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MyUserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'spipu.user.action.confirm' => 'onAccountConfirmed',
            'spipu.user.action.password' => 'onPasswordChanged',
        ];
    }

    public function onAccountConfirmed(UserEvent $event): void
    {
        $user = $event->getUser();
        // e.g., send a welcome email
    }

    public function onPasswordChanged(UserEvent $event): void
    {
        $user = $event->getUser();
        // e.g., notify the user of the change
    }
}
```

> **Note:** Login and logout are handled by Symfony's own security event system (`LoginSuccessEvent`, `LoginFailureEvent`), not by `UserEvent`. The bundle's internal `UserLoginSubscriber` listens to those events to update `nbLogin` and `nbTryLogin` on the entity.

## Routes Reference

All routes are registered via PHP attributes. The bundle's `routes.yaml` uses `type: attribute` with prefix `/`.

### Security routes (`SecurityController`)

| Route name | Path | Methods | Description |
|------------|------|---------|-------------|
| `spipu_user_security_login` | `/login` | GET, POST | Login form |
| `spipu_user_security_logout` | `/logout` | GET | Logout (handled by Symfony) |

### Account routes (`AccountController`, prefix `/account`)

| Route name | Path | Methods | Description |
|------------|------|---------|-------------|
| `spipu_user_account_create` | `/account/create` | GET, POST | Self-registration form |
| `spipu_user_account_create_waiting` | `/account/create-waiting` | GET | "Check your email" page after registration |
| `spipu_user_account_create_confirm` | `/account/confirm/{email}/{token}` | GET | Activation link target |
| `spipu_user_account_recover` | `/account/recovery` | GET, POST | Password recovery form |
| `spipu_user_account_recovery_waiting` | `/account/recovery-waiting` | GET | "Check your email" page after recovery request |
| `spipu_user_account_recovery_confirm` | `/account/new-password/{email}/{token}` | GET, POST | New password form (recovery link target) |

### Profile routes (`ProfileController`, prefix `/my-profile`)

| Route name | Path | Methods | Access |
|------------|------|---------|--------|
| `spipu_user_profile_show` | `/my-profile/` | GET | `ROLE_USER` |
| `spipu_user_profile_edit` | `/my-profile/edit` | GET, POST | `ROLE_USER` + fully authenticated |
| `spipu_user_profile_password` | `/my-profile/password` | GET, POST | `ROLE_USER` + fully authenticated |

### Admin routes (`AdminUserController`, prefix `/user`)

| Route name | Path | Methods | Required role |
|------------|------|---------|--------------|
| `spipu_user_admin_list` | `/user/` | GET | `ROLE_ADMIN_MANAGE_USER_SHOW` |
| `spipu_user_admin_show` | `/user/show/{id}` | GET | `ROLE_ADMIN_MANAGE_USER_SHOW` |
| `spipu_user_admin_create` | `/user/create/` | GET, POST | `ROLE_ADMIN_MANAGE_USER_EDIT` |
| `spipu_user_admin_edit` | `/user/edit/{id}` | GET, POST | `ROLE_ADMIN_MANAGE_USER_EDIT` |
| `spipu_user_admin_acl` | `/user/update-acl/{id}` | POST | `ROLE_ADMIN_MANAGE_USER_SHOW` |
| `spipu_user_admin_enable` | `/user/enable/{id}/{backTo}` | GET | `ROLE_ADMIN_MANAGE_USER_EDIT` |
| `spipu_user_admin_disable` | `/user/disable/{id}/{backTo}` | GET | `ROLE_ADMIN_MANAGE_USER_EDIT` |
| `spipu_user_admin_reset` | `/user/reset/{id}` | GET | `ROLE_ADMIN_MANAGE_USER_EDIT` |
| `spipu_user_admin_delete` | `/user/delete/{id}` | DELETE | `ROLE_ADMIN_MANAGE_USER_DELETE` |
| `spipu_user_admin_mass_enable` | `/user/mass-enable` | POST | `ROLE_ADMIN_MANAGE_USER_EDIT` |
| `spipu_user_admin_mass_disable` | `/user/mass-disable` | POST | `ROLE_ADMIN_MANAGE_USER_EDIT` |

[back](./README.md)
