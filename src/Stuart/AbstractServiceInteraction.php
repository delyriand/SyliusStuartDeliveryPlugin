<?php

/*
 * This file is part of Monsieur Biz' Stuart delivery plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusStuartDeliveryPlugin\Stuart;

use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use MonsieurBiz\SyliusStuartDeliveryPlugin\Helper\LoggerAwareTrait;
use MonsieurBiz\SyliusStuartDeliveryPlugin\Helper\ShipmentHelperTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

abstract class AbstractServiceInteraction implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    use ShipmentHelperTrait;

    private LoggerInterface $logger;

    private ClientInterface $client;

    private SettingsInterface $stuartDeliverySettings;

    private ChannelContextInterface $channelContext;

    private LocaleContextInterface $localeContext;

    public function __construct(
        ClientInterface $client,
        SettingsInterface $stuartDeliverySettings,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext
    ) {
        $this->logger = new NullLogger();
        $this->client = $client;
        $this->stuartDeliverySettings = $stuartDeliverySettings;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
    }

    protected function getSettingValue(string $path): ?string
    {
        return $this->stuartDeliverySettings->getCurrentValue(
            $this->channelContext->getChannel(),
            $this->localeContext->getLocaleCode(),
            $path
        );
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getCLient(): ClientInterface
    {
        return $this->client;
    }

    protected function getPickupAddress(): string
    {
        return $this->getClient()->getOnlineAddress(
            $this->getSettingValue('address') ?? '',
            $this->getSettingValue('postcode') ?? '',
            $this->getSettingValue('city') ?? ''
        );
    }

    protected function getDropOffAddress(AddressInterface $shippingAddress): string
    {
        return $this->getClient()->getOnlineAddress(
            $shippingAddress->getStreet() ?? '',
            $shippingAddress->getPostcode() ?? '',
            $shippingAddress->getCity() ?? ''
        );
    }
}
