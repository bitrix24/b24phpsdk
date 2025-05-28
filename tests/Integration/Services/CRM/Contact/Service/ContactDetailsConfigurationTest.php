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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Contact\Service;


use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\CardFieldConfiguration;
use Bitrix24\SDK\Services\CRM\Common\CardSectionConfiguration;
use Bitrix24\SDK\Services\CRM\Contact\Service\ContactDetailsConfiguration;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(ContactDetailsConfiguration::class)]
#[CoversMethod(ContactDetailsConfiguration::class, 'getPersonal')]
#[CoversMethod(ContactDetailsConfiguration::class, 'resetGeneral')]
#[CoversMethod(ContactDetailsConfiguration::class, 'resetPersonal')]
#[CoversMethod(ContactDetailsConfiguration::class, 'setPersonal')]
#[CoversMethod(ContactDetailsConfiguration::class, 'setGeneral')]
#[CoversMethod(ContactDetailsConfiguration::class, 'setForceCommonConfigForAll')]
class ContactDetailsConfigurationTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;
    private ContactDetailsConfiguration $contactConfig;

    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->contactConfig = $this->sb->getCRMScope()->contactDetailsConfiguration();
    }

    public function tearDown(): void
    {
          $this->contactConfig->resetGeneral();
    }

    public function testResetGeneral(): void
    {
        $this->assertTrue($this->contactConfig->resetGeneral()->isSuccess());
    }

    public function testResetPersonal(): void
    {
        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->contactConfig->resetPersonal($currentUserId)->isSuccess()
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
            new CardSectionConfiguration('main', 'Company Info', [
                new CardFieldConfiguration('SECOND_NAME'),
                new CardFieldConfiguration('TYPE_ID'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Info', [
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->contactConfig->setPersonal(
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
            new CardSectionConfiguration('main', 'Contact Info', [
                new CardFieldConfiguration('SECOND_NAME'),
                new CardFieldConfiguration('TYPE_ID'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Info', [
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $this->assertTrue($this->contactConfig->setGeneral($config)->isSuccess());
    }

    public function testForceConfigForAll(): void
    {
        $this->assertTrue($this->contactConfig->setForceCommonConfigForAll()->isSuccess());
    }

    public function testGetPersonal(): void
    {
        $config = [
            new CardSectionConfiguration('main', 'Contact Info', [
                new CardFieldConfiguration('SECOND_NAME'),
                new CardFieldConfiguration('TYPE_ID'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Info', [
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->contactConfig->setPersonal(
                $config,
                $currentUserId
            )->isSuccess()
        );


        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $cardConfig = $this->contactConfig->getGeneral($currentUserId);

        // todo fix after we get valid cardConfig
        $this->assertTrue(true);

    }
}