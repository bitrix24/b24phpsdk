<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Item\Service;


use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\CardFieldConfiguration;
use Bitrix24\SDK\Services\CRM\Common\CardSectionConfiguration;
use Bitrix24\SDK\Services\CRM\Item\Service\ItemDetailsConfiguration;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(ItemDetailsConfiguration::class)]
#[CoversMethod(ItemDetailsConfiguration::class, 'getPersonal')]
#[CoversMethod(ItemDetailsConfiguration::class, 'resetGeneral')]
#[CoversMethod(ItemDetailsConfiguration::class, 'resetPersonal')]
#[CoversMethod(ItemDetailsConfiguration::class, 'setPersonal')]
#[CoversMethod(ItemDetailsConfiguration::class, 'setGeneral')]
#[CoversMethod(ItemDetailsConfiguration::class, 'setForceCommonConfigForAll')]
class ItemDetailsConfigurationTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    public const ENTITY_TYPE_ID = 2;

    private ServiceBuilder $sb;

    private ItemDetailsConfiguration $itemConfig;

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->itemConfig = $this->sb->getCRMScope()->itemDetailsConfiguration();
    }

    protected function tearDown(): void
    {
          $this->itemConfig->resetGeneral(self::ENTITY_TYPE_ID);
    }

    public function testResetGeneral(): void
    {
        $this->assertTrue($this->itemConfig->resetGeneral(self::ENTITY_TYPE_ID)->isSuccess());
    }

    public function testResetPersonal(): void
    {
        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->itemConfig->resetPersonal(self::ENTITY_TYPE_ID, $currentUserId)->isSuccess()
        );
    }

    /**
     * @throws TransportException
     * @throws InvalidArgumentException
     * @throws BaseException
     */
    public function testSetPersonal(): void
    {
        $config = [
            new CardSectionConfiguration('main', 'About the Deal', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('OPPORTUNITY_WITH_CURRENCY'),
                new CardFieldConfiguration('STAGE_ID'),
                new CardFieldConfiguration('BEGINDATE'),
                new CardFieldConfiguration('CLOSEDATE'),
                new CardFieldConfiguration('CLIENT'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Information', [
                new CardFieldConfiguration('TYPE_ID'),
                new CardFieldConfiguration('SOURCE_ID'),
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->itemConfig->setPersonal(
                $config,
                self::ENTITY_TYPE_ID,
                $currentUserId
            )->isSuccess()
        );
    }

    /**
     * @throws TransportException
     * @throws InvalidArgumentException
     * @throws BaseException
     */
    public function testSetGeneral(): void
    {
        $config = [
            new CardSectionConfiguration('main', 'About the Deal', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('OPPORTUNITY_WITH_CURRENCY'),
                new CardFieldConfiguration('STAGE_ID'),
                new CardFieldConfiguration('BEGINDATE'),
                new CardFieldConfiguration('CLOSEDATE'),
                new CardFieldConfiguration('CLIENT'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Information', [
                new CardFieldConfiguration('TYPE_ID'),
                new CardFieldConfiguration('SOURCE_ID'),
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $this->assertTrue($this->itemConfig->setGeneral($config, self::ENTITY_TYPE_ID)->isSuccess());
    }

    public function testForceConfigForAll(): void
    {
        $this->assertTrue($this->itemConfig->setForceCommonConfigForAll(self::ENTITY_TYPE_ID)->isSuccess());
    }

    public function testGetPersonal(): void
    {
        $config = [
            new CardSectionConfiguration('main', 'About the Deal', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('OPPORTUNITY_WITH_CURRENCY'),
                new CardFieldConfiguration('STAGE_ID'),
                new CardFieldConfiguration('BEGINDATE'),
                new CardFieldConfiguration('CLOSEDATE'),
                new CardFieldConfiguration('CLIENT'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Information', [
                new CardFieldConfiguration('TYPE_ID'),
                new CardFieldConfiguration('SOURCE_ID'),
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->itemConfig->setPersonal(
                $config,
                self::ENTITY_TYPE_ID,
                $currentUserId
            )->isSuccess()
        );

        $cardConfigurationsResult = $this->itemConfig->getPersonal(self::ENTITY_TYPE_ID, $currentUserId);
        $this->assertIsArray($cardConfigurationsResult->getSections());
    }
}
