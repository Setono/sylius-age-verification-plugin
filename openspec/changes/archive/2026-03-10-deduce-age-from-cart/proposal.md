## Why

The `InitiateVerificationAction` controller currently receives the minimum age as a URL route parameter (`/age-verification/initiate/{age}`), passed from the Twig template. This is redundant since the minimum age can already be computed from the cart via `MinimumAgeChecker`. It also exposes the age value in the URL where it could be manually altered, which is a code cleanliness concern.

## What Changes

- **BREAKING** Remove `AuthorizationUrlGenerator` and `AuthorizationUrlGeneratorInterface` — no longer needed
- **BREAKING** Remove the `setono_sylius_age_verification__authorization_url` Twig function
- Modify `InitiateVerificationAction` to deduce the minimum age from the cart using `MinimumAgeCheckerInterface` and `CartContextInterface`, instead of accepting it as a route parameter
- Remove `{age}` from the initiation route (becomes `/age-verification/initiate`)
- Update the Twig template to use `path()` directly instead of the removed Twig function
- Handle the edge case where no age-restricted items are in the cart by redirecting back (Referer header, fallback to `sylius_shop_checkout_complete`)

## Capabilities

### New Capabilities

(none)

### Modified Capabilities

- `verifyid-integration`: The initiation controller no longer accepts age as a route parameter; it deduces it from the cart. The `AuthorizationUrlGenerator` abstraction is removed.

## Impact

- **Controllers**: `InitiateVerificationAction` gains two new dependencies (`CartContextInterface`, `MinimumAgeCheckerInterface`), loses the `$age` parameter
- **Services**: `AuthorizationUrlGenerator`, `AuthorizationUrlGeneratorInterface` deleted; service wiring in `services.xml` updated
- **Twig**: `Extension`, `Runtime` simplified (one fewer function/method/dependency); template updated
- **Routes**: `global.yaml` route path changes (breaking for any external links to the old URL)
- **Tests**: Unit tests for `InitiateVerificationAction` and `AuthorizationUrlGenerator` need updating/removal
