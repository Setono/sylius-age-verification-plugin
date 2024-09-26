<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Tests\Application\UrlGenerator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class DummyUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(private readonly string $url)
    {
    }

    public function setContext(RequestContext $context): void
    {
        // no op
    }

    public function getContext(): RequestContext
    {
        return new RequestContext();
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->url;
    }
}
