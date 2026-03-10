<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Controller;

use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class InitiateVerificationAction extends AbstractAction
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ChannelContextInterface $channelContext,
        private readonly string $pluginKey,
    ) {
    }

    public function __invoke(Request $request, int $age): RedirectResponse
    {
        $minimumAge = MinimumAge::from($age);

        $deviceId = Uuid::v4()->toRfc4122();
        $request->getSession()->set('verifyid_device_id', $deviceId);

        $callbackUrl = sprintf('https://%s%s', rtrim((string) $this->channelContext->getChannel()->getHostname(), '/'), $this->urlGenerator->generate('setono_sylius_age_verification_shop_callback'));

        $checkoutCompleteUrl = $this->urlGenerator->generate('sylius_shop_checkout_complete');

        try {
            $response = $this->httpClient->request('GET', sprintf(
                'https://app.verifyid.dk/api/url_generator_s/%s/%s/%d',
                $this->pluginKey,
                $deviceId,
                $minimumAge->value,
            ), [
                'query' => ['domain' => $callbackUrl],
            ]);

            $data = $response->toArray(false);
            $url = $data['url'] ?? null;

            if (!\is_string($url) || $response->getStatusCode() !== 200) {
                self::addFlash($request, 'error', 'setono_sylius_age_verification.ui.age_verification_error');

                return new RedirectResponse($checkoutCompleteUrl);
            }
        } catch (\Throwable) {
            self::addFlash($request, 'error', 'setono_sylius_age_verification.ui.age_verification_error');

            return new RedirectResponse($checkoutCompleteUrl);
        }

        return new RedirectResponse($url);
    }
}
