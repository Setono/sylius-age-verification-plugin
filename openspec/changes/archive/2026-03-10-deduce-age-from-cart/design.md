## Context

Currently, `InitiateVerificationAction` receives the minimum age as a route parameter (`{age}`). The Twig template computes the age from the cart via `MinimumAgeChecker`, passes it to `AuthorizationUrlGenerator` which embeds it in the URL, and the controller just reads it back out. This round-trip through the URL is unnecessary since the controller can compute the age directly from the cart.

## Goals / Non-Goals

**Goals:**
- Move age computation into the controller, removing it from the URL
- Remove the `AuthorizationUrlGenerator` abstraction that is no longer needed
- Handle edge case where the initiation route is accessed without age-restricted items in the cart

**Non-Goals:**
- Changing how `MinimumAgeChecker` works
- Changing the VerifyID API integration or callback flow
- Changing the Twig `minimumAgeCheck()` function (still needed for template display)

## Decisions

**Decision**: Inject `CartContextInterface` and `MinimumAgeCheckerInterface` into `InitiateVerificationAction`.

The controller will call `MinimumAgeChecker::check()` on the current cart to get the `MinimumAge`. This reuses the existing checker which already handles country filtering and prior verification checks.

**Decision**: Remove `AuthorizationUrlGenerator` and `AuthorizationUrlGeneratorInterface` entirely.

The only consumer was the Twig `Runtime`, and the only thing it did was generate a route URL with the age parameter. With the age removed from the route, the template can use Twig's built-in `path()` function directly. Keeping a one-line wrapper adds no value.

**Decision**: Use Referer header for redirect-back, with `sylius_shop_checkout_complete` as fallback.

When the controller finds no age-restricted items (checker returns `null`), it redirects back. The Referer header covers the normal flow (user came from checkout). The fallback to `sylius_shop_checkout_complete` handles missing Referer. This matches the existing error-handling pattern already used in the controller.

## Risks / Trade-offs

- [Breaking change: URL structure] External systems or bookmarks pointing to `/age-verification/initiate/{age}` will break → Low risk since this URL is transient (generated per-session) and not meant to be bookmarked.
- [Breaking change: Twig function removed] Any template overriding `_age_verification.html.twig` that uses `setono_sylius_age_verification__authorization_url()` will break → Acceptable since this is a plugin-internal template.
