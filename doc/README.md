# Spipu User Bundle

The **UserBundle** provides a complete user management system for Symfony applications: registration, authentication, password recovery, admin CRUD, role-based access control, login attempt tracking, and email notifications.

## Documentation

- [Installation](./install.md)
- [Configuration](./configuration.md)
- [Roles Reference](./roles.md)

## Features

- **User entity** (`AbstractUser`) — email, username, first/last name, active flag, login counters (`nbLogin`, `nbTryLogin`)
- **Authentication** — Symfony Security integration (form login, `UserChecker`, remember-me)
- **Registration flow** — optional self-registration with email activation link and token-based confirmation
- **Password recovery** — optional forgot-password flow with token-based email link
- **Admin UI** — user list, show, creation, editing, enable/disable, deletion, role assignment, and password reset at `/user/`
- **Login tracking** — `nbLogin` incremented on success; `nbTryLogin` incremented on failure (via Symfony security events)
- **Account locking** — automatic account lockout after a configurable number of failed login attempts (via ConfigurationBundle)
- **Password policy** — configurable minimum password length, enforced on registration, recovery, and password change
- **Email change notification** — when a user changes their email, a notification is sent to the previous email address
- **Role hierarchy** — contributes `ROLE_ADMIN_MANAGE_USER_SHOW`, `ROLE_ADMIN_MANAGE_USER_EDIT`, `ROLE_ADMIN_MANAGE_USER_DELETE`, `ROLE_ADMIN_MANAGE_USER`
- **Events** — `UserEvent` dispatched on registration, confirmation, password recovery, profile edit, and password change
- **Console commands** — `spipu:user:enable` and `spipu:user:disable` to manage accounts from the CLI
- **Module configuration** — behavior (entity class, feature flags) driven by DI-wired `ModuleConfiguration` service

## Requirements

- PHP 8.1+
- Symfony 6.4+
- `spipu/core-bundle`
- `spipu/ui-bundle`
- `spipu/configuration-bundle`
- Doctrine ORM
- Symfony Mailer

## Quick Start

```bash
composer require spipu/user-bundle
```

See [Installation](./install.md) for the full setup.
