<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration;

use Symfony\Component\HttpClient\HttpClient;
use Webmozart\Assert\Assert;

final class OpenIdConfigurationFactory implements OpenIdConfigurationFactoryInterface
{
    public function __construct(private readonly string $verifyDomain)
    {
    }

    public function create(): OpenIdConfiguration
    {
        $httpClient = HttpClient::create();
        $openIdConfiguration = $httpClient
            ->request('GET', sprintf('https://%s/.well-known/openid-configuration', $this->verifyDomain))
            ->toArray()
        ;

        Assert::keyExists($openIdConfiguration, 'token_endpoint');
        Assert::keyExists($openIdConfiguration, 'authorization_endpoint');
        Assert::keyExists($openIdConfiguration, 'jwks_uri');

        /** @var mixed $tokenEndpoint */
        $tokenEndpoint = $openIdConfiguration['token_endpoint'];
        Assert::stringNotEmpty($tokenEndpoint);

        /** @var mixed $authorizationEndpoint */
        $authorizationEndpoint = $openIdConfiguration['authorization_endpoint'];
        Assert::stringNotEmpty($authorizationEndpoint);

        /** @var mixed $jwksUri */
        $jwksUri = $openIdConfiguration['jwks_uri'];
        Assert::stringNotEmpty($jwksUri);

        $keys = $httpClient
            ->request('GET', $jwksUri)
            ->toArray()
        ;

        return new OpenIdConfiguration($authorizationEndpoint, $tokenEndpoint, $keys);
    }
}
