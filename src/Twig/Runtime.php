<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Twig;

use Setono\SyliusAgeVerificationPlugin\Checker\MinimumAgeCheckerInterface;
use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Setono\SyliusAgeVerificationPlugin\UrlGenerator\AuthorizationUrlGeneratorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Webmozart\Assert\Assert;

final class Runtime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly MinimumAgeCheckerInterface $minimumAgeChecker,
        private readonly CartContextInterface $cartContext,
        private readonly AuthorizationUrlGeneratorInterface $authorizationUrlGenerator,
    ) {
    }

    public function minimumAgeCheck(OrderInterface $order = null): ?MinimumAge
    {
        if (null === $order) {
            $order = $this->cartContext->getCart();
            Assert::isInstanceOf($order, OrderInterface::class);
        }

        return $this->minimumAgeChecker->check($order);
    }

    public function authorizationUrl(MinimumAge $age): string
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        Assert::isInstanceOf($order, OrderInterface::class);

        $countryCode = $order->getShippingAddress()?->getCountryCode();
        Assert::notNull($countryCode);

        return $this->authorizationUrlGenerator->generateUrl($age, $countryCode);
    }
}
