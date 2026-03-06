<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Setono\SyliusAgeVerificationPlugin\Controller\InitiateVerificationAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InitiateVerificationActionTest extends TestCase
{
    public function testItRedirectsToVerifyIdUrl(): void
    {
        $mockResponse = new MockResponse(json_encode(['url' => 'https://verifyid.dk/verify/123'], \JSON_THROW_ON_ERROR));
        $httpClient = new MockHttpClient($mockResponse);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnMap([
            ['setono_sylius_age_verification_shop_callback', [], UrlGeneratorInterface::ABSOLUTE_URL, 'https://shop.example.com/age-verification/callback'],
            ['sylius_shop_checkout_complete', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/checkout/complete'],
        ]);

        $action = new InitiateVerificationAction($httpClient, $urlGenerator, 'test_plugin_key');

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request, 18);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('https://verifyid.dk/verify/123', $response->getTargetUrl());
        self::assertNotNull($request->getSession()->get('verifyid_device_id'));
    }

    public function testItRedirectsToCheckoutOnApiError(): void
    {
        $mockResponse = new MockResponse(json_encode(['url' => null], \JSON_THROW_ON_ERROR), ['http_code' => 404]);
        $httpClient = new MockHttpClient($mockResponse);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $action = new InitiateVerificationAction($httpClient, $urlGenerator, 'test_plugin_key');

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request, 18);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }
}
