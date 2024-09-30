<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareCustomerInterface;
use Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration\OpenIdConfiguration;
use Setono\SyliusAgeVerificationPlugin\Token\TokenDecoderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class CriiptoCallbackAction
{
    use ORMTrait;

    public function __construct(
        private readonly OpenIdConfiguration $openIdConfiguration,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TokenDecoderInterface $tokenDecoder,
        private readonly CartContextInterface $cartContext,
        ManagerRegistry $managerRegistry,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        Assert::isInstanceOf($order, OrderInterface::class);

        /** @var AgeAwareCustomerInterface $customer */
        $customer = $order->getCustomer();
        Assert::isInstanceOf($customer, AgeAwareCustomerInterface::class);

        $client = HttpClient::create();
        $data = $client->request('POST', $this->openIdConfiguration->tokenEndpoint, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(sprintf('%s:%s', urlencode($this->clientId), urlencode($this->clientSecret))),
            ],
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $request->query->get('code'),
                'client_id' => $this->clientId,
                'redirect_uri' => $this->urlGenerator->generate(
                    name: 'setono_sylius_age_verification_shop_criipto_callback',
                    referenceType:  UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            ],
        ])->toArray();

        Assert::keyExists($data, 'id_token');
        Assert::stringNotEmpty($data['id_token']);

        $decodedToken = $this->tokenDecoder->decode($data['id_token'], $this->openIdConfiguration);

        // todo validate decoded token

        $customer->setOlderThan($decodedToken->olderThan());
        $customer->setAgeCheckedAt(new \DateTimeImmutable());

        $this->getManager($customer)->flush();

        return new RedirectResponse('https://127.0.0.1:8000/en_US/checkout/complete');
    }
}
