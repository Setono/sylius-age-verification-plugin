# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sylius plugin that adds age verification to checkout using [Criipto](https://www.criipto.com/) as the OpenID Connect-based age verification provider. Products can have a minimum age requirement (15, 16, 18, or 21), and customers are verified via Criipto's identity verification before completing checkout.

## Commands

- **Run tests:** `composer phpunit` or `vendor/bin/phpunit`
- **Run single test:** `vendor/bin/phpunit tests/Path/To/TestFile.php --filter testMethodName`
- **Check coding standards:** `composer check-style`
- **Fix coding standards:** `composer fix-style`
- **Static analysis (Psalm):** `composer analyse` (error level 1, PHP 8.1)
- **Rector:** `vendor/bin/rector process --dry-run`
- **Mutation testing:** `vendor/bin/infection`
- **Dependency analysis:** `vendor/bin/composer-dependency-analyser`
- **Lint container:** `(cd tests/Application && bin/console lint:container)`
- **Validate Doctrine schema:** `(cd tests/Application && bin/console doctrine:schema:validate -vvv)`

## Architecture

### Namespace: `Setono\SyliusAgeVerificationPlugin`

**OpenID Connect flow with Criipto:**
1. `Checker/MinimumAgeChecker` — inspects an order's items for the highest `minimumAge` across products, considering the shipping country (only `enabled_countries` trigger verification) and whether the customer was already verified
2. `UrlGenerator/AuthorizationUrlGenerator` — builds the Criipto authorization URL with the appropriate `is_over_X` scope
3. `Controller/CriiptoCallbackAction` — handles the OAuth callback, exchanges the authorization code for a token, decodes it, and stores the verification result on the customer
4. `Token/TokenDecoder` — decodes the JWT ID token using Criipto's JWKS keys, maps it to `DecodedToken` via Valinor

**Model traits (must be applied to Sylius entities by the host application):**
- `AgeAwareProductTrait` / `AgeAwareProductInterface` — adds `minimumAge` field to Product
- `AgeAwareCustomerTrait` / `AgeAwareCustomerInterface` — adds `olderThan` and `ageCheckedAt` fields to Customer

**Twig integration:**
- `Twig/Extension` + `Twig/Runtime` — provides helpers used in the shop checkout template to show the age verification prompt
- Templates hook into Sylius UI via `sylius.shop.checkout.complete.before_navigation` event (configured in the extension's `prepend()`)

**Configuration (`setono_sylius_age_verification`):**
- `enabled_countries` (required) — list of country codes where age verification is enforced
- `criipto.client_id`, `criipto.client_secret`, `criipto.verify_domain` — default to env vars `CRIIPTO_CLIENT_ID`, `CRIIPTO_CLIENT_SECRET`, `CRIIPTO_VERIFY_DOMAIN`

### Service wiring

Services are defined in `src/Resources/config/services.xml` (not autowired). All services use FQCN as service IDs with interface aliases.

### Test Application

`tests/Application/` contains a full Sylius application used for integration testing and container/schema validation. It includes model overrides (`Customer`, `Product`) that apply the age-aware traits.

## Code Style

- Uses Sylius Labs coding standard via ECS (`ecs.php` imports `vendor/sylius-labs/coding-standard/ecs.php`)
- All PHP files use `declare(strict_types=1)`
- PHP 8.1 minimum (Rector enforces `UP_TO_PHP_81`)
- Psalm at error level 1
