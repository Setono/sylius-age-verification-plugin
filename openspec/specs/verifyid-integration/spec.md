## ADDED Requirements

### Requirement: Initiate age verification via VerifyID
The system SHALL provide a controller action that initiates the VerifyID age verification flow. It SHALL generate a UUID v4 device_id, store it in the Symfony session, call the VerifyID `/url_generator_s/{pluginKey}/{device_id}/{age}?domain={callbackUrl}` endpoint, and redirect the user to the returned verification URL.

#### Scenario: Successful verification initiation
- **WHEN** a user clicks the "Verify Age" button on the checkout page with a minimum age of 18
- **THEN** the system generates a UUID v4 device_id, stores it in the session, calls `GET https://api.verifyid.dk/api/url_generator_s/{pluginKey}/{device_id}/18?domain={callbackUrl}`, and redirects the user to the URL returned in the response

#### Scenario: Successful verification initiation for age 16
- **WHEN** a user clicks the "Verify Age" button on the checkout page with a minimum age of 16
- **THEN** the system calls the VerifyID endpoint with age parameter `16` and redirects to the returned URL

#### Scenario: VerifyID API returns error
- **WHEN** the VerifyID URL generator endpoint returns a non-200 response or returns `url: null`
- **THEN** the system redirects the user back to the checkout complete page (does not crash)

### Requirement: Handle VerifyID callback
The system SHALL provide a callback controller action at a public route. When VerifyID redirects the user back after verification, the callback SHALL read the `token_age_verified` query parameter, retrieve the `device_id` from the session, call the VerifyID `/auth_check/{token}/{device_id}` endpoint, and store the verified age on the customer.

#### Scenario: Successful age verification callback
- **WHEN** the user is redirected back with a valid `token_age_verified` query parameter
- **THEN** the system calls `GET https://api.verifyid.dk/api/auth_check/{token}/{device_id}`, receives `{ "age": 18 }`, sets `olderThan = 18` and `ageCheckedAt = now` on the customer, and redirects to the checkout complete page

#### Scenario: Callback with null age (verification failed)
- **WHEN** the VerifyID `/auth_check/` endpoint returns `{ "age": null }`
- **THEN** the system does NOT update the customer's age verification fields, and redirects to the checkout complete page (the age check on the checkout page will still show the verification prompt)

#### Scenario: Callback without token parameter
- **WHEN** the callback URL is accessed without a `token_age_verified` query parameter
- **THEN** the system redirects to the checkout complete page without updating any customer data

#### Scenario: Callback without device_id in session
- **WHEN** the callback is accessed but no `device_id` exists in the session
- **THEN** the system redirects to the checkout complete page without updating any customer data

### Requirement: Plugin configuration with VerifyID credentials
The system SHALL accept a `verify_id` configuration block with a `plugin_key` setting. The `plugin_key` SHALL default to the `VERIFYID_PLUGIN_KEY` environment variable.

#### Scenario: Configuration with environment variable
- **WHEN** the plugin is configured without explicitly setting `plugin_key`
- **THEN** the system uses the value of `%env(VERIFYID_PLUGIN_KEY)%`

#### Scenario: Configuration with explicit value
- **WHEN** the plugin configuration sets `verify_id.plugin_key: "my_key"`
- **THEN** the system uses `"my_key"` as the VerifyID plugin key

### Requirement: Only ages 16 and 18 are supported
The `MinimumAge` enum SHALL only contain cases for ages 16 and 18.

#### Scenario: Product with minimum age 16
- **WHEN** a product has minimum age set to 16
- **THEN** the system initiates verification with age parameter 16

#### Scenario: Product with minimum age 18
- **WHEN** a product has minimum age set to 18
- **THEN** the system initiates verification with age parameter 18

### Requirement: Server-side device_id generation
The system SHALL generate the VerifyID device_id server-side as a UUID v4 string, without requiring any client-side JavaScript. The device_id SHALL be stored in the Symfony session and reused for the auth_check call after callback.

#### Scenario: Device ID persists across redirect
- **WHEN** the initiation controller generates a device_id and stores it in the session
- **THEN** the callback controller retrieves the same device_id from the session to use in the `/auth_check/` API call

### Requirement: Twig runtime generates internal route URL
The Twig runtime `authorizationUrl()` method SHALL return a URL to the internal initiation controller (not directly to VerifyID). The initiation controller route SHALL include the minimum age as a parameter.

#### Scenario: Template renders verify button
- **WHEN** the checkout complete page renders and an age check is required for age 18
- **THEN** the "Verify Age" link points to the internal initiation route with age 18 as a parameter
