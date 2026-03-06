## Context

The plugin currently uses Criipto as an OpenID Connect provider for age verification. The OIDC flow involves discovering endpoints via `.well-known/openid-configuration`, building authorization URLs with scopes like `is_over_18`, exchanging authorization codes for JWT id_tokens, and decoding those tokens with JWKS keys.

VerifyID offers a simpler REST API: generate a verification URL, redirect the user, receive a token back, and check the token against an endpoint. No OIDC, no JWTs, no key management.

The current codebase has these Criipto-specific components:
- `OpenIdConfiguration` / `OpenIdConfigurationFactory` — fetches OIDC discovery document and JWKS keys
- `TokenDecoder` / `DecodedToken` — decodes JWT id_tokens using firebase/php-jwt
- `AuthorizationUrlGenerator` — builds OIDC authorization URLs with scopes
- `CriiptoCallbackAction` — exchanges auth code for token, decodes it, stores result
- `Configuration` — defines `criipto` config block with `client_id`, `client_secret`, `verify_domain`

## Goals / Non-Goals

**Goals:**
- Replace Criipto with VerifyID as the age verification provider
- Keep the entire flow server-side (no client-side JavaScript required)
- Simplify the integration — fewer moving parts, fewer dependencies
- Maintain the existing checkout UX pattern (button on checkout complete page)

**Non-Goals:**
- Supporting multiple providers simultaneously
- Supporting provider abstraction/interface for swapping providers
- Backward compatibility with Criipto configuration
- Supporting ages 15 or 21 (not supported by VerifyID)

## Decisions

### 1. Server-side device_id generation

**Decision**: Generate UUID v4 server-side and store in the Symfony session.

**Alternatives considered**:
- Client-side JS via VerifyID's `device_id.js` script — would require AJAX or form submission to pass the device_id to the server, complicating the template
- Passing device_id through URL parameters — exposes it unnecessarily

**Rationale**: The `device_id.js` script just generates a UUID v4 and stores it in localStorage. We can generate the same UUID server-side with `Uuid::v4()`. Storing it in the session keeps it available across the redirect flow (initiate → VerifyID → callback) without any client-side dependencies.

### 2. Two-phase redirect flow

**Decision**: The "Verify Age" button links to a new `InitiateVerificationAction` controller that generates the device_id, calls VerifyID's URL generator API, and redirects the user to the returned URL.

**Alternatives considered**:
- Pre-generating the verification URL when the checkout page renders (in the Twig runtime) — this would require an HTTP call on every page load, even if the user never clicks verify
- AJAX-based URL generation — adds client-side complexity

**Rationale**: Lazy URL generation (only when the user clicks) avoids unnecessary API calls. The Twig runtime now generates a URL to our own controller rather than directly to the provider, keeping the template simple.

```
Current:  Template → renders direct Criipto URL → user clicks → Criipto → callback
New:      Template → renders internal route → user clicks → InitiateAction
          → calls VerifyID API → redirects to verification URL → VerifyID → callback
```

### 3. Callback reads token_age_verified parameter

**Decision**: The callback controller reads `token_age_verified` from the query string, retrieves the `device_id` from the session, and calls VerifyID's `/auth_check/{token}/{device_id}` endpoint.

**Rationale**: This matches VerifyID's documented flow. The response `{ "age": int|null }` maps directly to the existing `Customer::setOlderThan()` method.

### 4. Rename controller and route

**Decision**: Rename `CriiptoCallbackAction` to `VerifyIdCallbackAction`. Add `InitiateVerificationAction`. Rename routes to `setono_sylius_age_verification_shop_initiate` and `setono_sylius_age_verification_shop_callback`.

**Rationale**: Provider-specific naming in routes and controllers. The route names drop the `criipto` prefix to avoid coupling to any specific provider name, since the plugin is already age-verification-specific.

### 5. Configuration simplification

**Decision**: Replace the `criipto` config node (3 values) with `verify_id` node containing only `plugin_key`. Default to `%env(VERIFYID_PLUGIN_KEY)%`.

**Rationale**: VerifyID only requires a plugin key. No client secret, no domain discovery.

### 6. Remove MinimumAge cases 15 and 21

**Decision**: Reduce `MinimumAge` enum to `MINIMUM_16 = 16` and `MINIMUM_18 = 18`. Remove `asScope()` method (was Criipto-specific).

**Rationale**: VerifyID's `/url_generator_s/` endpoint only accepts ages 16 and 18. The `asScope()` method was for building OIDC scope strings, which no longer applies.

### 7. Dependency cleanup

**Decision**: Remove `firebase/php-jwt` and `cuyz/valinor` from composer.json. Add `symfony/uid` if not already available (for `Uuid::v4()`).

**Rationale**: JWT decoding and Valinor mapping were only used for Criipto token handling. `symfony/uid` provides UUID generation and is likely already available through Sylius dependencies.

## Risks / Trade-offs

- **[VerifyID API availability]** The plugin now depends on VerifyID's API being available for both URL generation and age checking. Criipto used a client-side redirect flow that only needed the server for token exchange. → Mitigation: Both calls are short-lived HTTP GETs. The initiate call only happens when the user clicks verify. Error handling should show a clear message if the API is unreachable.

- **[Session dependency]** The device_id is stored in session, which means the flow breaks if the session is lost between initiation and callback (e.g., session cookie expires, load balancer issues). → Mitigation: Standard Symfony session handling; same risk as any CSRF-token-based flow.

- **[Age 16/18 only]** Removing ages 15 and 21 is a breaking change for host apps that configured products with those minimum ages. → Mitigation: This is a major version bump (2.x branch). Document the migration clearly.

- **[No test mode toggle]** VerifyID has a test key (`verifyid_test`) and test endpoint (`/auth_check_test/`). We're not building an explicit test mode toggle. → Mitigation: Users can set `VERIFYID_PLUGIN_KEY=verifyid_test` for testing. The `/auth_check_test/` endpoint can be addressed later if needed.
