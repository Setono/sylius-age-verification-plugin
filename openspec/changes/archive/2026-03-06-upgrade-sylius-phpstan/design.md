## Context

The plugin currently uses Psalm (level 1) for static analysis and targets Sylius ~1.12. The Setono plugin ecosystem has standardized on PHPStan via `setono/sylius-plugin-pack`, which bundles Sylius, PHPStan (with Symfony/Doctrine extensions), ECS, Rector, Infection, and PHPUnit as a single metapackage. The skeleton repository's 1.14.x branch serves as the reference implementation.

Current dev dependencies that will be replaced:
- `sylius/sylius: ~1.12.19` — direct Sylius dependency
- `setono/code-quality-pack: ^2.8.3` — bundles Psalm, ECS, Rector, etc.
- `psalm/plugin-phpunit: ^0.18.4` — Psalm PHPUnit plugin
- `infection/infection: ^0.27.11` — standalone infection
- `phpunit/phpunit: ^9.6.20` — standalone phpunit

All of these are consolidated into `setono/sylius-plugin-pack: ~1.14.1`.

## Goals / Non-Goals

**Goals:**
- Align with the Setono plugin skeleton 1.14.x branch conventions
- Replace Psalm with PHPStan (level max) including Symfony and Doctrine integrations
- Upgrade test application to work with Sylius ~1.14
- Add PHP 8.3 to CI matrix
- Update CI workflow to match skeleton conventions

**Non-Goals:**
- Adding Symfony 5.4 support (keeping current `^6.4 || ^7.0` range)
- Upgrading to PHPUnit 10+ (the plugin pack still uses PHPUnit ^9)
- Changing any plugin functionality or behavior
- Upgrading to Sylius 2.x

## Decisions

### Use `setono/sylius-plugin-pack` instead of individual dev dependencies

The plugin pack consolidates all dev tooling into a single versioned metapackage. This ensures consistency across Setono plugins and simplifies upgrades. The alternative (managing individual dependencies) is more flexible but creates drift between plugins.

### PHPStan at level `max` with Symfony and Doctrine extensions

The skeleton uses level `max` which is stricter than Psalm level 1. PHPStan's Symfony extension provides container-aware analysis (console commands, service types), and the Doctrine extension understands entity mappings. This requires two bootstrap files (`tests/PHPStan/console_application.php` and `tests/PHPStan/object_manager.php`) that boot the test application kernel.

### Keep Symfony version range as `^6.4 || ^7.0`

The skeleton supports `^5.4 || ^6.4 || ^7.0`, but this plugin's actual dependencies (Sylius bundles at `^1.0`) don't require 5.4 support, and the CI matrix would become unnecessarily large. The Symfony 5.4 range can be added later if needed.

### Convert `@psalm-suppress` to `@phpstan-ignore` on a case-by-case basis

Two source files have `@psalm-suppress` annotations. These will be evaluated individually — some may be removable entirely if PHPStan handles the patterns differently, others will need `@phpstan-ignore` equivalents.

## Risks / Trade-offs

- **PHPStan level max may surface many new errors** → Run PHPStan after the migration and fix or suppress errors iteratively. The strictness is intentional and matches the skeleton convention.
- **Sylius 1.14 may have breaking bundle changes** → The test application configuration is based directly on the working skeleton, minimizing risk. The `bundles.php` needs `SyliusStateMachineAbstractionBundle` and `SyliusCalendarBundle` added.
- **Doctrine annotation mapping in test app** → Current config uses `type: annotation`. The entities already use PHP 8 attributes (`#[ORM\...]`), so this should be updated to `type: attribute` for consistency.
