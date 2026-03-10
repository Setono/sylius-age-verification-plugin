<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareCustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

final class VerifyIdCallbackAction extends AbstractAction
{
    use ORMTrait;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CartContextInterface $cartContext,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $checkoutCompleteUrl = $this->urlGenerator->generate('sylius_shop_checkout_complete');

        $token = $request->query->getString('token_age_verified');
        if ('' === $token) {
            return new RedirectResponse($checkoutCompleteUrl);
        }

        $deviceId = $request->getSession()->get('verifyid_device_id');
        if (!\is_string($deviceId) || '' === $deviceId) {
            return new RedirectResponse($checkoutCompleteUrl);
        }

        $response = $this->httpClient->request('GET', sprintf(
            'https://app.verifyid.dk/api/auth_check/%s/%s',
            $token,
            $deviceId,
        ));

        $data = $response->toArray(false);
        $age = $data['age'] ?? null;

        if (!\is_int($age)) {
            return new RedirectResponse($checkoutCompleteUrl);
        }

        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        Assert::isInstanceOf($order, OrderInterface::class);

        $customer = $order->getCustomer();
        Assert::isInstanceOf($customer, AgeAwareCustomerInterface::class);

        $customer->setOlderThan($age);
        $customer->setAgeCheckedAt(new \DateTimeImmutable());

        $this->getManager($customer)->flush();

        self::addFlash($request, 'success', 'setono_sylius_age_verification.ui.age_verification_success');

        return new RedirectResponse($checkoutCompleteUrl);
    }
}
