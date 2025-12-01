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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service;


use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\CardFieldConfiguration;
use Bitrix24\SDK\Services\CRM\Common\CardSectionConfiguration;
use Bitrix24\SDK\Services\CRM\Deal\Service\DealDetailsConfiguration;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(DealDetailsConfiguration::class)]
#[CoversMethod(DealDetailsConfiguration::class, 'getPersonal')]
#[CoversMethod(DealDetailsConfiguration::class, 'resetGeneral')]
#[CoversMethod(DealDetailsConfiguration::class, 'resetPersonal')]
#[CoversMethod(DealDetailsConfiguration::class, 'setPersonal')]
#[CoversMethod(DealDetailsConfiguration::class, 'setGeneral')]
#[CoversMethod(DealDetailsConfiguration::class, 'setForceCommonConfigForAll')]
class DealDetailsConfigurationTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private DealDetailsConfiguration $dealConfig;

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->dealConfig = $this->sb->getCRMScope()->dealDetailsConfiguration();
    }

    protected function tearDown(): void
    {
          $this->dealConfig->resetGeneral();
    }

    public function testResetGeneral(): void
    {
        $this->assertTrue($this->dealConfig->resetGeneral()->isSuccess());
    }

    public function testResetPersonal(): void
    {
        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->dealConfig->resetPersonal($currentUserId)->isSuccess()
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
            $this->dealConfig->setPersonal(
                $config,
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

        $this->assertTrue($this->dealConfig->setGeneral($config)->isSuccess());
    }

    public function testForceConfigForAll(): void
    {
        $this->assertTrue($this->dealConfig->setForceCommonConfigForAll()->isSuccess());
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
            $this->dealConfig->setPersonal(
                $config,
                $currentUserId
            )->isSuccess()
        );


        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $cardConfigurationsResult = $this->dealConfig->getPersonal($currentUserId);
        $this->assertIsArray($cardConfigurationsResult->getSections());
    }
}
