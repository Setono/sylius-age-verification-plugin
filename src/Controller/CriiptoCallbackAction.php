<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Controller;

use Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration\OpenIdConfiguration;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CriiptoCallbackAction
{
    public function __construct(
        private readonly OpenIdConfiguration $openIdConfiguration,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $client = HttpClient::create();
        $response = $client->request('POST', $this->openIdConfiguration->tokenEndpoint, [
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
        ]);

        dd($response->getContent(false));
    }
}
