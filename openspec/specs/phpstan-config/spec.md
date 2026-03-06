## ADDED Requirements

### Requirement: PHPStan configuration file

The project SHALL have a `phpstan.neon` configuration file at the repository root with analysis level `max`. It SHALL analyze `src` and `tests` directories and exclude `tests/Application/*`. It SHALL enable Symfony and Doctrine integrations via `symfony.consoleApplicationLoader` and `doctrine.objectManagerLoader` pointing to bootstrap files under `tests/PHPStan/`.

#### Scenario: PHPStan analyzes plugin source code
- **WHEN** `composer analyse` is run
- **THEN** PHPStan SHALL analyze all PHP files in `src/` and `tests/` (excluding `tests/Application/`) at level max

#### Scenario: Symfony integration is active
- **WHEN** PHPStan runs
- **THEN** it SHALL use the test application kernel to resolve container services and console commands

#### Scenario: Doctrine integration is active
- **WHEN** PHPStan runs
- **THEN** it SHALL use the test application's Doctrine object manager to resolve entity mappings

### Requirement: PHPStan bootstrap files

The project SHALL have `tests/PHPStan/console_application.php` that boots the test application kernel and returns a Symfony Console Application instance. The project SHALL have `tests/PHPStan/object_manager.php` that boots the test application kernel and returns the Doctrine object manager.

#### Scenario: Console application bootstrap
- **WHEN** PHPStan loads `tests/PHPStan/console_application.php`
- **THEN** it SHALL receive a fully booted `Symfony\Bundle\FrameworkBundle\Console\Application` instance

#### Scenario: Object manager bootstrap
- **WHEN** PHPStan loads `tests/PHPStan/object_manager.php`
- **THEN** it SHALL receive a fully booted Doctrine `ObjectManager` instance

### Requirement: Psalm removal

The project SHALL NOT contain `psalm.xml` or any `@psalm-suppress` annotations in source code. All Psalm-specific annotations SHALL be converted to PHPStan equivalents or removed if unnecessary.

#### Scenario: No Psalm configuration remains
- **WHEN** the migration is complete
- **THEN** `psalm.xml` SHALL NOT exist and no PHP files in `src/` SHALL contain `@psalm-suppress` annotations
