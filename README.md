# Age Verification Plugin for Sylius

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Mutation testing][ico-infection]][link-infection]

A plugin to add age verification to your Sylius store using [Criipto](https://www.criipto.com/) as the age verification provider.

During checkout, if the customer's cart contains products with a minimum age requirement and the shipping address is in an enabled country, the plugin prompts the customer to verify their age via Criipto before completing the order. Supported minimum age thresholds are 15, 16, 18, and 21.

## Prerequisites

You need a [Criipto](https://www.criipto.com/) account. From the Criipto dashboard, obtain your:

- **Client ID**
- **Client Secret**
- **Verify Domain** (e.g. `your-tenant.criipto.id`)

You must also register the callback URL in your Criipto application settings. The callback URL is:

```
https://your-domain.com/criipto/callback
```

## Installation

### Step 1: Install the plugin

```bash
composer require setono/sylius-age-verification-plugin
```

### Step 2: Register the bundle

Add the plugin to your `config/bundles.php` file:

```php
return [
    // ...
    Setono\SyliusAgeVerificationPlugin\SetonoSyliusAgeVerificationPlugin::class => ['all' => true],
    // ...
];
```

### Step 3: Configure environment variables

Add the following environment variables to your `.env.local`:

```dotenv
CRIIPTO_CLIENT_ID=your-client-id
CRIIPTO_CLIENT_SECRET=your-client-secret
CRIIPTO_VERIFY_DOMAIN=your-tenant.criipto.id
```

### Step 4: Import routes

Create `config/routes/setono_sylius_age_verification.yaml`:

```yaml
setono_sylius_age_verification:
    resource: "@SetonoSyliusAgeVerificationPlugin/Resources/config/routes.yaml"
```

### Step 5: Configure the plugin

Create `config/packages/setono_sylius_age_verification.yaml`:

```yaml
setono_sylius_age_verification:
    enabled_countries:
        - DK # Add the country codes where age verification should be enforced
```

### Step 6: Extend entities

#### Extend the `Customer` entity

```php
<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareCustomerInterface;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareCustomerTrait;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_customer')]
class Customer extends BaseCustomer implements AgeAwareCustomerInterface
{
    use AgeAwareCustomerTrait;
}
```

Register it in your Sylius configuration (e.g. `config/packages/_sylius.yaml`):

```yaml
sylius_customer:
    resources:
        customer:
            classes:
                model: App\Entity\Customer\Customer
```

#### Extend the `Product` entity

```php
<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareProductInterface;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareProductTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct implements AgeAwareProductInterface
{
    use AgeAwareProductTrait;
}
```

Register it in your Sylius configuration (e.g. `config/packages/_sylius.yaml`):

```yaml
sylius_product:
    resources:
        product:
            classes:
                model: App\Entity\Product\Product
```

### Step 7: Update your database schema

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

## Usage

Once installed, an **Age verification** tab appears on the product edit page in the Sylius admin. Set a minimum age (15, 16, 18, or 21) for any product that requires age verification.

During checkout, if the order contains age-restricted products and the shipping address is in one of the configured `enabled_countries`, the customer will be prompted to verify their age via Criipto before they can complete the order. Once verified, the result is stored on the customer entity and reused for future orders.



[ico-version]: https://poser.pugx.org/setono/sylius-age-verification-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-age-verification-plugin/license
[ico-github-actions]: https://github.com/Setono/sylius-age-verification-plugin/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/sylius-age-verification-plugin/branch/1.x/graph/badge.svg
[ico-infection]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fsylius-age-verification-plugin%2F1.x

[link-packagist]: https://packagist.org/packages/setono/sylius-age-verification-plugin
[link-github-actions]: https://github.com/Setono/sylius-age-verification-plugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/sylius-age-verification-plugin
[link-infection]: https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-age-verification-plugin/1.x
