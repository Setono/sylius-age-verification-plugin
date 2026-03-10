## MODIFIED Requirements

### Requirement: Initiate age verification via VerifyID
The system SHALL provide a controller action that initiates the VerifyID age verification flow. It SHALL determine the minimum age by inspecting the current cart via `MinimumAgeCheckerInterface`. It SHALL generate a UUID v4 device_id, store it in the Symfony session, call the VerifyID `/url_generator_s/{pluginKey}/{device_id}/{age}?domain={callbackUrl}` endpoint, and redirect the user to the returned verification URL. If the checker returns no minimum age (no age-restricted items, non-applicable country, or already verified), the controller SHALL redirect back to the referring page (via Referer header) or to the checkout complete page as fallback.

#### Scenario: Successful verification initiation
- **WHEN** a user clicks the "Verify Age" button on the checkout page and the cart contains a product with minimum age 18
- **THEN** the system computes the minimum age from the cart, generates a UUID v4 device_id, stores it in the session, calls `GET https://api.verifyid.dk/api/url_generator_s/{pluginKey}/{device_id}/18?domain={callbackUrl}`, and redirects the user to the URL returned in the response

#### Scenario: Successful verification initiation for age 16
- **WHEN** a user clicks the "Verify Age" button and the cart contains a product with minimum age 16
- **THEN** the system computes the minimum age as 16 from the cart and calls the VerifyID endpoint with age parameter `16`

#### Scenario: VerifyID API returns error
- **WHEN** the VerifyID URL generator endpoint returns a non-200 response or returns `url: null`
- **THEN** the system redirects the user back to the checkout complete page with an error flash message

#### Scenario: No age-restricted items in cart
- **WHEN** a user navigates to the initiation route but the cart contains no age-restricted items (or the checker returns null)
- **THEN** the system redirects the user back to the Referer URL, or to the checkout complete page if no Referer is present

## REMOVED Requirements

### Requirement: Twig runtime generates internal route URL
**Reason**: The `AuthorizationUrlGenerator` abstraction and the `authorizationUrl()` Twig function are no longer needed. The initiation route no longer takes parameters, so the template SHALL use Twig's built-in `path()` function directly.
**Migration**: Replace `{{ setono_sylius_age_verification__authorization_url(minimumAge) }}` with `{{ path('setono_sylius_age_verification_shop_initiate') }}` in any template overrides.
