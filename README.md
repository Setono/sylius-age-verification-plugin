# Age Verification Plugin for Sylius

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Mutation testing][ico-infection]][link-infection]

A plugin to add age verification to your Sylius store. Right now the plugin only works with [Criipto](https://www.criipto.com/) as the age verification provider.

## Installation

```bash
composer require setono/sylius-age-verification-plugin
```

Add plugin to your `config/bundles.php` file:

```php
return [
    // ...
    Setono\SyliusAgeVerificationPlugin\SetonoSyliusAgeVerificationPlugin::class => ['all' => true],
    // ...
];
```

## Extend entities

### Extend `Customer` entity

### Extend `Product` entity



[ico-version]: https://poser.pugx.org/setono/sylius-age-verification-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-age-verification-plugin/license
[ico-github-actions]: https://github.com/Setono/sylius-age-verification-plugin/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/sylius-age-verification-plugin/branch/master/graph/badge.svg
[ico-infection]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2FSyliusPluginSkeleton%2Fmaster

[link-packagist]: https://packagist.org/packages/setono/sylius-age-verification-plugin
[link-github-actions]: https://github.com/Setono/sylius-age-verification-plugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/sylius-age-verification-plugin
[link-infection]: https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-age-verification-plugin/master
