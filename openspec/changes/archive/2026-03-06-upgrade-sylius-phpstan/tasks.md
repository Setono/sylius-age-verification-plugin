## 1. Update composer.json

- [x] 1.1 Replace dev dependencies: remove `sylius/sylius`, `setono/code-quality-pack`, `psalm/plugin-phpunit`, `infection/infection`, `phpunit/phpunit`; add `setono/sylius-plugin-pack: ~1.14.1` and `sylius-labs/polyfill-symfony-security: ^1.1.2`
- [x] 1.2 Add `phpstan/extension-installer: true` to `config.allow-plugins`
- [x] 1.3 Add `"audit": { "block-insecure": false }` to `config`
- [x] 1.4 Change `scripts.analyse` from `"psalm"` to `"phpstan analyse"`

## 2. Replace Psalm with PHPStan

- [x] 2.1 Delete `psalm.xml`
- [x] 2.2 Create `phpstan.neon` with level max, src/tests paths, tests/Application exclusion, Symfony console loader, and Doctrine object manager loader
- [x] 2.3 Create `tests/PHPStan/console_application.php` that boots the test kernel and returns a Console Application
- [x] 2.4 Create `tests/PHPStan/object_manager.php` that boots the test kernel and returns the Doctrine object manager
- [x] 2.5 Convert `@psalm-suppress` annotations in `src/DependencyInjection/Configuration.php` and `src/DependencyInjection/SetonoSyliusAgeVerificationExtension.php` to `@phpstan-ignore` equivalents or remove if unnecessary

## 3. Update test application for Sylius 1.14

- [x] 3.1 Update `tests/Application/config/bundles.php`: add `SyliusStateMachineAbstractionBundle`, ensure `SyliusCalendarBundle` is present, add `SyliusTestPlugin` for test/test_cached envs, add `SyliusLabsPolyfillSymfonySecurityBundle`, reorder to match skeleton
- [x] 3.2 Update `tests/Application/.env`: update `DATABASE_URL` serverVersion to match skeleton (`11.6.2-MariaDB`), clean up comments
- [x] 3.3 Update `tests/Application/config/packages/doctrine.yaml`: change mapping type from `annotation` to `attribute` (entities already use PHP 8 attributes)
- [x] 3.4 Review and update `tests/Application/config/packages/security.yaml` for Sylius 1.14 compatibility

## 4. Update CI workflow

- [x] 4.1 Update `.github/workflows/build.yaml`: add PHP 8.3 to test matrices
- [x] 4.2 Update actions versions: `actions/checkout@v4` → `@v5`
- [x] 4.3 Change static-code-analysis step from `vendor/bin/psalm --php-version=...` to `composer analyse`
- [x] 4.4 Remove `continue-on-error: true` from Rector step
- [x] 4.5 Update push branches trigger to include `"1.*.x"` pattern

## 5. Run and fix

- [x] 5.1 Run `composer update` to install new dependencies
- [x] 5.2 Run `composer analyse` (PHPStan) and fix or suppress any errors
- [x] 5.3 Run `composer check-style` and fix any style issues
- [x] 5.4 Run `(cd tests/Application && bin/console lint:container)` to validate the container
- [x] 5.5 Run `composer phpunit` to verify tests pass
