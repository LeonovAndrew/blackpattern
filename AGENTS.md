# Repository Guidelines

## Overview

This repository is a 1C-Bitrix site. Custom application code and the active
site template live under `local/`; `bitrix/` is the platform core and should
not be edited unless the task explicitly requires it.

## Project Layout

- `index.php` is the landing page and renders Bitrix components for FAQ and team data.
- `auth/` contains authentication-related pages.
- `local/templates/blackpattern/` contains the active template: shared header/footer,
  PHP component templates, CSS, JavaScript, and static HTML references.
- `local/cli/` contains idempotent setup scripts for Bitrix information blocks and
  their content. Run these from the repository root.
- `upload/` contains runtime/user-uploaded content; do not modify it for source changes.

## Development Conventions

- Preserve the Bitrix bootstrap and layout flow: include the prolog before using Bitrix
  APIs and use the standard `bitrix/header.php` and `bitrix/footer.php` wrappers for pages.
- Keep customizations in `local/`. Prefer component templates over changes to framework core.
- Follow the surrounding PHP style: 4-space indentation, short arrays (`[]`), and explicit
  error handling for administrative/setup scripts.
- Keep setup scripts idempotent: look up existing entities before creating or updating them.
- Preserve the existing file encoding when editing Russian-language content.

## Validation

- Check modified PHP files with `php -l <file>`.
- For changes to setup scripts, review that rerunning the script updates existing data rather
  than duplicating it.
- For front-end work, verify the landing page in a configured local Bitrix environment and
  check browser console errors.

## Change Safety

- Do not edit `bitrix/`, generated cache files, or `upload/` unless the requested change
  specifically targets them.
- Do not commit credentials, environment-specific configuration, or production data.
- Keep unrelated working-tree changes intact.
