<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Tests\Unit\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAgeVerificationPlugin\Controller\VerifyIdCallbackAction;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareCustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface TestCustomerInterface extends CustomerInterface, AgeAwareCustomerInterface
{
}

final class VerifyIdCallbackActionTest extends TestCase
{
    public function testItStoresVerifiedAge(): void
    {
        $mockResponse = new MockResponse(json_encode(['age' => 18], \JSON_THROW_ON_ERROR));
        $httpClient = new MockHttpClient($mockResponse);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $customer = $this->createMock(TestCustomerInterface::class);
        $customer->expects(self::once())->method('setOlderThan')->with(18);
        $customer->expects(self::once())->method('setAgeCheckedAt');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($order);

        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->expects(self::once())->method('flush');

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($objectManager);

        $action = new VerifyIdCallbackAction($httpClient, $urlGenerator, $cartContext, $managerRegistry);

        $request = new Request(['token_age_verified' => 'abc123']);
        $session = new Session(new MockArraySessionStorage());
        $session->set('verifyid_device_id', 'test-device-id');
        $request->setSession($session);

        $response = $action($request);

        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }

    public function testItRedirectsWithoutTokenParameter(): void
    {
        $httpClient = new MockHttpClient();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $cartContext = $this->createMock(CartContextInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $action = new VerifyIdCallbackAction($httpClient, $urlGenerator, $cartContext, $managerRegistry);

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request);

        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }

    public function testItRedirectsWithoutDeviceIdInSession(): void
    {
        $httpClient = new MockHttpClient();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $cartContext = $this->createMock(CartContextInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $action = new VerifyIdCallbackAction($httpClient, $urlGenerator, $cartContext, $managerRegistry);

        $request = new Request(['token_age_verified' => 'abc123']);
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $action($request);

        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }

    public function testItRedirectsWhenAgeIsNull(): void
    {
        $mockResponse = new MockResponse(json_encode(['age' => null], \JSON_THROW_ON_ERROR));
        $httpClient = new MockHttpClient($mockResponse);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/checkout/complete');

        $cartContext = $this->createMock(CartContextInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $action = new VerifyIdCallbackAction($httpClient, $urlGenerator, $cartContext, $managerRegistry);

        $request = new Request(['token_age_verified' => 'abc123']);
        $session = new Session(new MockArraySessionStorage());
        $session->set('verifyid_device_id', 'test-device-id');
        $request->setSession($session);

        $response = $action($request);

        self::assertSame('/checkout/complete', $response->getTargetUrl());
    }
}
