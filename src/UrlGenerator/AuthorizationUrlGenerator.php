<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\UrlGenerator;

use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration\OpenIdConfiguration;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AuthorizationUrlGenerator implements AuthorizationUrlGeneratorInterface
{
    public function __construct(
        private readonly OpenIdConfiguration $openIdConfiguration,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $clientId,
    ) {
    }

    public function generateUrl(MinimumAge $age, string $countryCode): string
    {
        // todo add nonce and state
        $query = [
            'scope' => sprintf('openid %s', $age->asScope()),
            'client_id' => $this->clientId,
            'redirect_uri' => $this->urlGenerator->generate(
                name: 'setono_sylius_age_verification_shop_criipto_callback',
                referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            'response_type' => 'code',
            'response_mode' => 'query',
            'nonce' => 'ecnon-dc3c110c-49b3-4deb-96aa-d73b0743cbb0',
            'state' => 'state',
            'acr_values' => 'urn:age-verification',
            'login_hint' => sprintf('country:%s', $countryCode),
        ];

        return sprintf(
            '%s?%s',
            $this->openIdConfiguration->authorizationEndpoint,
            http_build_query(data: $query, encoding_type: \PHP_QUERY_RFC3986),
        );
    }
}
