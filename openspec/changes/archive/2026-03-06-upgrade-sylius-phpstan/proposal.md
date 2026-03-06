## Why

The plugin currently targets Sylius ~1.12 and uses Psalm for static analysis. Sylius 1.14 is the current stable release with improved APIs, and the Setono plugin ecosystem has standardized on PHPStan via `setono/sylius-plugin-pack`. Aligning with the skeleton's 1.14.x branch brings the plugin up to date and ensures consistency across the organization's plugins.

## What Changes

- **BREAKING**: Minimum Sylius version moves from ~1.12 to ~1.14
- Replace Psalm with PHPStan (level max) for static analysis, including Symfony and Doctrine integrations
- Replace `sylius/sylius: ~1.12.19` and `setono/code-quality-pack` with `setono/sylius-plugin-pack: ~1.14.1` in dev dependencies
- Add PHP 8.3 to CI test matrix
- Update test application (`tests/Application/`) bundles and configuration for Sylius 1.14 compatibility
- Update CI workflow to match skeleton conventions (actions versions, matrix expansion, PHPStan instead of Psalm)
- Convert `@psalm-suppress` annotations to `@phpstan-ignore` equivalents in source code

## Capabilities

### New Capabilities

- `phpstan-config`: PHPStan configuration with Symfony/Doctrine integration and bootstrap files (`phpstan.neon`, `tests/PHPStan/`)

### Modified Capabilities

_(none — no spec-level behavioral requirements change; this is a tooling and dependency upgrade)_

## Impact

- **Dependencies**: `composer.json` require-dev section substantially changes; `psalm.xml` removed, `phpstan.neon` added
- **CI**: `.github/workflows/build.yaml` updated with new matrix, actions versions, and static analysis tool
- **Source code**: Two files have `@psalm-suppress` annotations that need converting
- **Test application**: `tests/Application/config/bundles.php`, `.env`, `config/packages/` files updated for Sylius 1.14
