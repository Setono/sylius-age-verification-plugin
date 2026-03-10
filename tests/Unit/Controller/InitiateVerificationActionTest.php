<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Setono\SyliusAgeVerificationPlugin\Checker\MinimumAgeCheckerInterface;
use Setono\SyliusAgeVerificationPlugin\Controller\InitiateVerificationAction;
use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InitiateVerificationActionTest extends TestCase
{
    public function testItRedirectsToVerifyIdUrl(): void
    {
        $mockResponse = new MockResponse(json_encode(['url' => 'https://verifyid.dk/verify/123'], \JSON_THROW_ON_ERROR));
        $httpClient = new MockHttpClient($mockResponse);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnMap([
            ['setono_sylius_age_verification_shop_callback', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/age-verification/callback'],
            ['sylius_shop_checkout_complete', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/checkout/complete'],
        ]);

        $action = new InitiateVerificationAction(
            $httpClient,
            $urlGenerator,
            $this->createChannelContext(),
            $this->createCartContext(),
            $this->createMinimumAgeChecker(MinimumAge::from(18)),
            'test_plugin_key',
        );

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request);

        self::assertSame('https://verifyid.dk/verify/123', $response->getTargetUrl());
        self::assertNotNull($request->getSession()->get('verifyid_device_id'));
    }

    public function testItRedirectsToCheckoutOnApiError(): void
    {
        $mockResponse = new MockResponse(json_encode(['url' => null], \JSON_THROW_ON_ERROR), ['http_code' => 404]);
        $httpClient = new MockHttpClient($mockResponse);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $action = new InitiateVerificationAction(
            $httpClient,
            $urlGenerator,
            $this->createChannelContext(),
            $this->createCartContext(),
            $this->createMinimumAgeChecker(MinimumAge::from(18)),
            'test_plugin_key',
        );

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request);

        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }

    public function testItRedirectsToRefererWhenNoAgeRestrictedItems(): void
    {
        $httpClient = new MockHttpClient();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $action = new InitiateVerificationAction(
            $httpClient,
            $urlGenerator,
            $this->createChannelContext(),
            $this->createCartContext(),
            $this->createMinimumAgeChecker(null),
            'test_plugin_key',
        );

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->headers->set('referer', '/some-other-page');

        $response = $action($request);

        self::assertSame('/some-other-page', $response->getTargetUrl());
    }

    public function testItFallsBackToCheckoutCompleteWhenNoReferer(): void
    {
        $httpClient = new MockHttpClient();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $action = new InitiateVerificationAction(
            $httpClient,
            $urlGenerator,
            $this->createChannelContext(),
            $this->createCartContext(),
            $this->createMinimumAgeChecker(null),
            'test_plugin_key',
        );

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request);

        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }

    public function testItRedirectsBackWhenCartIsNotAnOrder(): void
    {
        $httpClient = new MockHttpClient();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $cart = $this->createMock(\Sylius\Component\Order\Model\OrderInterface::class);
        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($cart);

        $action = new InitiateVerificationAction(
            $httpClient,
            $urlGenerator,
            $this->createChannelContext(),
            $cartContext,
            $this->createMinimumAgeChecker(null),
            'test_plugin_key',
        );

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request);

        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }

    private function createChannelContext(string $hostname = 'shop.example.com'): ChannelContextInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getHostname')->willReturn($hostname);

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        return $channelContext;
    }

    private function createCartContext(): CartContextInterface
    {
        $cart = $this->createMock(OrderInterface::class);

        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($cart);

        return $cartContext;
    }

    private function createMinimumAgeChecker(?MinimumAge $result): MinimumAgeCheckerInterface
    {
        $checker = $this->createMock(MinimumAgeCheckerInterface::class);
        $checker->method('check')->willReturn($result);

        return $checker;
    }
}
