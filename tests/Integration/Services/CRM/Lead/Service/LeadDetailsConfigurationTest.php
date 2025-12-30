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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service;


use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\CardFieldConfiguration;
use Bitrix24\SDK\Services\CRM\Common\CardSectionConfiguration;
use Bitrix24\SDK\Services\CRM\Lead\Service\LeadDetailsConfiguration;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(LeadDetailsConfiguration::class)]
#[CoversMethod(LeadDetailsConfiguration::class, 'getPersonal')]
#[CoversMethod(LeadDetailsConfiguration::class, 'resetGeneral')]
#[CoversMethod(LeadDetailsConfiguration::class, 'resetPersonal')]
#[CoversMethod(LeadDetailsConfiguration::class, 'setPersonal')]
#[CoversMethod(LeadDetailsConfiguration::class, 'setGeneral')]
#[CoversMethod(LeadDetailsConfiguration::class, 'setForceCommonConfigForAll')]
class LeadDetailsConfigurationTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private LeadDetailsConfiguration $leadConfig;

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->leadConfig = $this->sb->getCRMScope()->leadDetailsConfiguration();
    }

    #[\Override]
    protected function tearDown(): void
    {
          $this->leadConfig->resetGeneral();
    }

    public function testResetGeneral(): void
    {
        $this->assertTrue($this->leadConfig->resetGeneral()->isSuccess());
    }

    public function testResetPersonal(): void
    {
        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->leadConfig->resetPersonal($currentUserId)->isSuccess()
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
            new CardSectionConfiguration('main', 'About the Lead', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('OPPORTUNITY'),
                new CardFieldConfiguration('SOURCE_ID'),
                new CardFieldConfiguration('SOURCE_DESCRIPTION'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Information', [
                new CardFieldConfiguration('STATUS_ID'),
                new CardFieldConfiguration('STATUS_DESCRIPTION'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->leadConfig->setPersonal(
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
            new CardSectionConfiguration('main', 'About the Lead', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('OPPORTUNITY'),
                new CardFieldConfiguration('SOURCE_ID'),
                new CardFieldConfiguration('SOURCE_DESCRIPTION'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Information', [
                new CardFieldConfiguration('STATUS_ID'),
                new CardFieldConfiguration('STATUS_DESCRIPTION'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $this->assertTrue($this->leadConfig->setGeneral($config)->isSuccess());
    }

    public function testForceConfigForAll(): void
    {
        $this->assertTrue($this->leadConfig->setForceCommonConfigForAll()->isSuccess());
    }

    public function testGetPersonal(): void
    {
        $config = [
            new CardSectionConfiguration('main', 'About the Lead', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('OPPORTUNITY'),
                new CardFieldConfiguration('SOURCE_ID'),
                new CardFieldConfiguration('SOURCE_DESCRIPTION'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Information', [
                new CardFieldConfiguration('STATUS_ID'),
                new CardFieldConfiguration('STATUS_DESCRIPTION'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->leadConfig->setPersonal(
                $config,
                $currentUserId
            )->isSuccess()
        );


        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $cardConfigurationsResult = $this->leadConfig->getPersonal($currentUserId);
        $this->assertIsArray($cardConfigurationsResult->getSections());
    }
}
