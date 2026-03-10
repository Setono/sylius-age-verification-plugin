# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sylius plugin that adds age verification to checkout using [VerifyID](https://verifyid.dk/) as the age verification provider. Products can have a minimum age requirement (16 or 18), and customers are verified via VerifyID before completing checkout.

## Commands

- **Run tests:** `composer phpunit` or `vendor/bin/phpunit`
- **Run single test:** `vendor/bin/phpunit tests/Path/To/TestFile.php --filter testMethodName`
- **Check coding standards:** `composer check-style`
- **Fix coding standards:** `composer fix-style`
- **Static analysis (PHPStan):** `composer analyse` (level max)
- **Rector:** `vendor/bin/rector process --dry-run`
- **Mutation testing:** `vendor/bin/infection`
- **Dependency analysis:** `vendor/bin/composer-dependency-analyser`
- **Lint container:** `(cd tests/Application && bin/console lint:container)`
- **Validate Doctrine schema:** `(cd tests/Application && bin/console doctrine:schema:validate -vvv)`

## Architecture

### Namespace: `Setono\SyliusAgeVerificationPlugin`

**VerifyID age verification flow:**
1. `Checker/MinimumAgeChecker` — inspects an order's items for the highest `minimumAge` across products, considering the shipping country (only `enabled_countries` trigger verification) and whether the customer was already verified
2. `Controller/InitiateVerificationAction` — generates a device ID, calls VerifyID's `url_generator_s` API to get a verification URL, and redirects the customer to it
3. `Controller/VerifyIdCallbackAction` — handles the callback from VerifyID, calls the `auth_check` API with the token and device ID, and stores the verified age on the customer

**Model traits (must be applied to Sylius entities by the host application):**
- `AgeAwareProductTrait` / `AgeAwareProductInterface` — adds `minimumAge` field to Product
- `AgeAwareCustomerTrait` / `AgeAwareCustomerInterface` — adds `olderThan` and `ageCheckedAt` fields to Customer

**Admin integration:**
- `Form/Extension/ProductTypeExtension` — adds a `minimumAge` choice field to the product form
- `EventSubscriber/Admin/AddAgeVerificationTabSubscriber` — adds an age verification tab to the product admin form

**Twig integration:**
- `Twig/Extension` + `Twig/Runtime` — provides helpers used in the shop checkout template to show the age verification prompt
- Templates hook into Sylius UI via `sylius.shop.checkout.complete.before_navigation` event (configured in the extension's `prepend()`)

**Configuration (`setono_sylius_age_verification`):**
- `enabled_countries` (required) — list of country codes where age verification is enforced
- `verify_id.plugin_key` — defaults to env var `VERIFYID_PLUGIN_KEY`

### Service wiring

Services are defined in `src/Resources/config/services.xml` (not autowired). All services use FQCN as service IDs with interface aliases.

### Test Application

`tests/Application/` contains a full Sylius application used for integration testing and container/schema validation. It includes model overrides (`Customer`, `Product`) that apply the age-aware traits.

**Local dev server:**
- Start: `(cd tests/Application && symfony serve -d)` — runs on https://127.0.0.1:8000
- Stop: `(cd tests/Application && symfony server:stop)`
- Status: `(cd tests/Application && symfony server:status)`
- Uses PHP FPM 8.1 via the Symfony CLI

**Frontend assets (must be built before the site works):**
- Use Node 18: `nvm use 18`
- Install deps: `(cd tests/Application && yarn install)`
- Dev build: `(cd tests/Application && yarn build)`
- Prod build: `(cd tests/Application && yarn build:prod)`
- Watch mode: `(cd tests/Application && yarn watch)`

**Browser testing with Playwright MCP:**
- The site runs at https://127.0.0.1:8000/en_US/ (self-signed TLS)
- Use `mcp__playwright__browser_navigate` to open pages
- Use `mcp__playwright__browser_snapshot` (preferred) or `mcp__playwright__browser_take_screenshot` to inspect the page

## Code Style

- Uses Sylius Labs coding standard via ECS (`ecs.php` imports `vendor/sylius-labs/coding-standard/ecs.php`)
- All PHP files use `declare(strict_types=1)`
- PHP 8.1 minimum (Rector enforces `UP_TO_PHP_81`)
- PHPStan at level max with Symfony and Doctrine extensions

## Git Conventions

- Do not use command substitution (`$(...)` or backticks) for commit messages. Use the `-m` flag with a plain string instead.
