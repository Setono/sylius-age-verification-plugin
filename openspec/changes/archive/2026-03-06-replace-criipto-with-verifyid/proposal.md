## Why

Replace Criipto (OpenID Connect-based) age verification provider with VerifyID (REST API-based). VerifyID provides a simpler integration model — no OIDC discovery, no JWT decoding, no client secrets — just a plugin key and two REST endpoints. This also aligns the plugin with MitID-based verification via VerifyID's platform.

## What Changes

- **BREAKING**: Remove all Criipto/OIDC integration (OpenID configuration discovery, JWT token decoding, JWKS key fetching)
- **BREAKING**: Replace `criipto` configuration block (`client_id`, `client_secret`, `verify_domain`) with `verify_id` configuration (`plugin_key`)
- **BREAKING**: Remove `MinimumAge` cases for ages 15 and 21 — VerifyID only supports 16 and 18
- Replace direct-link-to-provider flow with two-phase server-side flow: user clicks link to own controller, controller calls VerifyID API to get verification URL, redirects user there
- New callback controller reads `token_age_verified` query parameter (instead of OIDC authorization code) and calls VerifyID `/auth_check/` endpoint to get verified age
- Generate `device_id` (UUID v4) server-side and store in session — no client-side JavaScript required
- Rename route from `criipto_callback` to provider-agnostic name

## Capabilities

### New Capabilities
- `verifyid-integration`: Server-side integration with VerifyID REST API for age verification (URL generation, callback handling, age checking)

### Modified Capabilities
- None

## Impact

- **Deleted files**: `OpenIdConfiguration`, `OpenIdConfigurationFactory`, `OpenIdConfigurationFactoryInterface`, `TokenDecoder`, `TokenDecoderInterface`, `DecodedToken`
- **Rewritten files**: `CriiptoCallbackAction` (renamed), `AuthorizationUrlGenerator`, `Configuration`, `SetonoSyliusAgeVerificationExtension`, `services.xml`, routes, shop template
- **Modified files**: `MinimumAge` enum (remove 15/21 cases, remove `asScope()`)
- **Dependencies**: Can remove `firebase/php-jwt` and potentially `cuyz/valinor` (used only for JWT token mapping). May need `symfony/uid` for UUID v4 generation.
- **Environment variables**: Remove `CRIIPTO_CLIENT_ID`, `CRIIPTO_CLIENT_SECRET`, `CRIIPTO_VERIFY_DOMAIN`. Add `VERIFYID_PLUGIN_KEY`.
- **Session**: New dependency on session storage for `device_id` persistence across the redirect flow
- **Breaking for host apps**: Config key changes, env var changes, `MinimumAge` enum cases removed
