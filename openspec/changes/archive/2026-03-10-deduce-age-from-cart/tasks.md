## 1. Route and Controller

- [x] 1.1 Remove `{age}` parameter from the initiation route in `src/Resources/config/routes/global.yaml`
- [x] 1.2 Update `InitiateVerificationAction` to inject `CartContextInterface` and `MinimumAgeCheckerInterface`, deduce the minimum age from the cart, and redirect back when no age-restricted items are found (Referer with `sylius_shop_checkout_complete` fallback)
- [x] 1.3 Update `services.xml`: add `sylius.context.cart` and `MinimumAgeChecker` arguments to `InitiateVerificationAction`

## 2. Remove AuthorizationUrlGenerator

- [x] 2.1 Delete `src/UrlGenerator/AuthorizationUrlGenerator.php` and `src/UrlGenerator/AuthorizationUrlGeneratorInterface.php`
- [x] 2.2 Remove `AuthorizationUrlGenerator` service and alias from `services.xml`

## 3. Twig Cleanup

- [x] 3.1 Remove `authorizationUrl()` method from `src/Twig/Runtime.php` and remove the `AuthorizationUrlGeneratorInterface` dependency
- [x] 3.2 Remove `setono_sylius_age_verification__authorization_url` function from `src/Twig/Extension.php`
- [x] 3.3 Remove `AuthorizationUrlGeneratorInterface` argument from the `Runtime` service in `services.xml`
- [x] 3.4 Update `src/Resources/views/shop/_age_verification.html.twig` to use `path('setono_sylius_age_verification_shop_initiate')` instead of the removed Twig function

## 4. Tests

- [x] 4.1 Update `tests/Unit/Controller/InitiateVerificationActionTest.php` for the new constructor signature and cart-based age deduction
- [x] 4.2 Remove or update any tests for `AuthorizationUrlGenerator` (none existed; removed reference from Kernel.php)
- [x] 4.3 Run `composer analyse` (PHPStan) and fix any issues
- [x] 4.4 Run `composer check-style` and fix any issues
