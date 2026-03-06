## 1. Delete Criipto/OIDC components

- [x] 1.1 Delete `src/OpenIdConfiguration/OpenIdConfiguration.php`, `OpenIdConfigurationFactory.php`, `OpenIdConfigurationFactoryInterface.php`
- [x] 1.2 Delete `src/Token/TokenDecoder.php`, `TokenDecoderInterface.php`, `DecodedToken.php`
- [x] 1.3 Delete `src/Controller/CriiptoCallbackAction.php`

## 2. Update MinimumAge enum

- [x] 2.1 Remove `MINIMUM_15` and `MINIMUM_21` cases from `MinimumAge` enum, remove `asScope()` method

## 3. Update configuration

- [x] 3.1 Replace `criipto` config node with `verify_id` node containing `plugin_key` (defaulting to `%env(VERIFYID_PLUGIN_KEY)%`) in `Configuration.php`
- [x] 3.2 Update `SetonoSyliusAgeVerificationExtension` to read `verify_id.plugin_key` and set corresponding container parameter

## 4. Create VerifyID controllers

- [x] 4.1 Create `InitiateVerificationAction` controller: generates UUID v4 device_id, stores in session, calls VerifyID `/url_generator_s/` endpoint, redirects to returned URL
- [x] 4.2 Create `VerifyIdCallbackAction` controller: reads `token_age_verified` query param, retrieves device_id from session, calls `/auth_check/` endpoint, stores verified age on customer

## 5. Update URL generator

- [x] 5.1 Rewrite `AuthorizationUrlGenerator` to generate an internal route URL (to `InitiateVerificationAction`) with the minimum age as a parameter, instead of building an external OIDC URL
- [x] 5.2 Update `AuthorizationUrlGeneratorInterface` if the signature changes

## 6. Update service wiring

- [x] 6.1 Update `services.xml`: remove Criipto/OIDC service definitions, add new controller services (`InitiateVerificationAction`, `VerifyIdCallbackAction`) with required dependencies (HttpClient, session, router, plugin_key parameter)
- [x] 6.2 Update `AuthorizationUrlGenerator` service definition (remove OpenIdConfiguration dependency, simplify to just router)

## 7. Update routes

- [x] 7.1 Update `routes/global.yaml`: replace `criipto_callback` route with two routes — `setono_sylius_age_verification_shop_initiate` (with age parameter) and `setono_sylius_age_verification_shop_callback`

## 8. Update Twig runtime

- [x] 8.1 Update `Twig/Runtime::authorizationUrl()` — it no longer needs `countryCode` since the URL is now an internal route. Simplify to pass just the `MinimumAge` to the generator.

## 9. Update dependencies

- [x] 9.1 Remove `firebase/php-jwt` and `cuyz/valinor` from `composer.json`, add `symfony/uid` if not already present
- [x] 9.2 Run `composer update` to verify dependency changes

## 10. Update tests and tooling

- [x] 10.1 Update or rewrite existing unit tests for changed components (`MinimumAgeChecker`, `AuthorizationUrlGenerator`, etc.)
- [x] 10.2 Add unit tests for `InitiateVerificationAction` and `VerifyIdCallbackAction`
- [x] 10.3 Update test application config (`tests/Application/`) — replace Criipto env vars with VerifyID plugin key
- [x] 10.4 Run `composer analyse` (PHPStan) and fix any errors
- [x] 10.5 Run `composer check-style` and fix any coding standard violations
- [x] 10.6 Run `vendor/bin/composer-dependency-analyser` and fix any issues
