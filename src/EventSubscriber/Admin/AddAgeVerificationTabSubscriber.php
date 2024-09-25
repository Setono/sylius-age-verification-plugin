<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\EventSubscriber\Admin;

use Sylius\Bundle\AdminBundle\Event\ProductMenuBuilderEvent;
use Sylius\Bundle\AdminBundle\Menu\ProductFormMenuBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AddAgeVerificationTabSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductFormMenuBuilder::EVENT_NAME => 'addTab',
        ];
    }

    public function addTab(ProductMenuBuilderEvent $event): void
    {
        $event->getMenu()
            ->addChild('age_verification')
            ->setAttribute('template', '@SetonoSyliusAgeVerificationPlugin/admin/product/tab/_age_verification.html.twig')
            ->setLabel('setono_sylius_age_verification.ui.age_verification')
        ;
    }
}
