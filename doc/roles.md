# User Bundle — Roles Reference

[back](./README.md)

## Roles Defined by This Bundle

The `RoleDefinition` class (tagged `spipu.user.role`) contributes the following roles to the application's role hierarchy via CoreBundle's role system:

| Role | Label key | Weight | Parent(s) |
|------|-----------|--------|-----------|
| `ROLE_ADMIN_MANAGE_USER_SHOW` | `spipu.user.role.admin_show` | 10 | `ROLE_ADMIN` |
| `ROLE_ADMIN_MANAGE_USER_EDIT` | `spipu.user.role.admin_edit` | 20 | `ROLE_ADMIN` |
| `ROLE_ADMIN_MANAGE_USER_DELETE` | `spipu.user.role.admin_delete` | 30 | `ROLE_ADMIN` |
| `ROLE_ADMIN_MANAGE_USER` | `spipu.user.role.admin` | 210 | `ROLE_ADMIN_MANAGE_USER_SHOW`, `ROLE_ADMIN_MANAGE_USER_EDIT`, `ROLE_ADMIN_MANAGE_USER_DELETE` |
| `ROLE_SUPER_ADMIN` | — | — | `ROLE_ADMIN_MANAGE_USER` (added as child) |

> `ROLE_USER`, `ROLE_ADMIN`, and `ROLE_SUPER_ADMIN` are not defined here — they come from the application or CoreBundle. This bundle only adds `ROLE_ADMIN_MANAGE_USER*` as children of `ROLE_ADMIN`, and adds `ROLE_ADMIN_MANAGE_USER` as a child of `ROLE_SUPER_ADMIN`.

## Role Hierarchy

```
ROLE_SUPER_ADMIN
  └── ROLE_ADMIN_MANAGE_USER
        ├── ROLE_ADMIN_MANAGE_USER_SHOW  (child: ROLE_ADMIN)
        ├── ROLE_ADMIN_MANAGE_USER_EDIT  (child: ROLE_ADMIN)
        └── ROLE_ADMIN_MANAGE_USER_DELETE (child: ROLE_ADMIN)
```

## What Each Role Grants

| Role | What it allows |
|------|---------------|
| `ROLE_ADMIN_MANAGE_USER_SHOW` | View user list (`/user/`) and user detail pages (`/user/show/{id}`); update ACL (`/user/update-acl/{id}`) |
| `ROLE_ADMIN_MANAGE_USER_EDIT` | Create, edit, enable, disable, reset password for users |
| `ROLE_ADMIN_MANAGE_USER_DELETE` | Delete users |
| `ROLE_ADMIN_MANAGE_USER` | All of the above |

## Role Assignment in Admin UI

Roles are assigned to users via the ACL panel on the user show page (`/user/show/{id}`). The `RoleService` reads all registered roles (from all bundles via `RoleDefinitionList`) and validates that only known role codes are submitted.

## AbstractUser Default Role

When a user entity has an empty `roles` array but `active = true`, `AbstractUser::getRoles()` returns `['ROLE_USER']` as a default. If `active = false`, `getRoles()` returns an empty array regardless of stored roles.

## Adding Custom Roles

Any bundle or the application can contribute additional roles by implementing `RoleDefinitionInterface` (from CoreBundle) and tagging the service `spipu.user.role`. See [CoreBundle documentation](../../CoreBundle/doc/) for details.

[back](./README.md)
