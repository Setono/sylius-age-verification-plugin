<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Token;

use CuyZ\Valinor\MapperBuilder;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration\OpenIdConfiguration;
use Webmozart\Assert\Assert;

final class TokenDecoder implements TokenDecoderInterface
{
    public function decode(string $token, OpenIdConfiguration $openIdConfiguration): DecodedToken
    {
        $data = (array) JWT::decode($token, JWK::parseKeySet($openIdConfiguration->keys, 'RS256'));
        Assert::keyExists($data, 'http://ageverification.criipto.com');
        $data['ageVerification'] = (array) $data['http://ageverification.criipto.com'];

        return (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->mapper()
            ->map(DecodedToken::class, $data)
        ;
    }
}
