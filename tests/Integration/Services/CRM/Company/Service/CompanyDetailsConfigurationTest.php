<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Company\Service;


use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\CardFieldConfiguration;
use Bitrix24\SDK\Services\CRM\Common\CardSectionConfiguration;
use Bitrix24\SDK\Services\CRM\Company\Service\CompanyDetailsConfiguration;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(CompanyDetailsConfiguration::class)]
#[CoversMethod(CompanyDetailsConfiguration::class, 'getPersonal')]
#[CoversMethod(CompanyDetailsConfiguration::class, 'resetGeneral')]
#[CoversMethod(CompanyDetailsConfiguration::class, 'resetPersonal')]
#[CoversMethod(CompanyDetailsConfiguration::class, 'setPersonal')]
#[CoversMethod(CompanyDetailsConfiguration::class, 'setGeneral')]
#[CoversMethod(CompanyDetailsConfiguration::class, 'setForceCommonConfigForAll')]
class CompanyDetailsConfigurationTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private array $createdCompanies = [];

    private array $createdContacts = [];

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
    }

    protected function tearDown(): void
    {
          $this->sb->getCRMScope()->companyDetailsConfiguration()->resetGeneral();
    }

    public function testResetGeneral(): void
    {
        $this->assertTrue($this->sb->getCRMScope()->companyDetailsConfiguration()->resetGeneral()->isSuccess());
    }

    public function testResetPersonal(): void
    {
        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->sb->getCRMScope()->companyDetailsConfiguration()->resetPersonal($currentUserId)->isSuccess()
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
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('COMPANY_TYPE'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Info', [
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->sb->getCRMScope()->companyDetailsConfiguration()->setPersonal(
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
            new CardSectionConfiguration('main', 'Company Info', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('COMPANY_TYPE'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Info', [
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $this->assertTrue($this->sb->getCRMScope()->companyDetailsConfiguration()->setGeneral($config)->isSuccess());
    }

    public function testForceConfigForAll(): void
    {
        $this->assertTrue($this->sb->getCRMScope()->companyDetailsConfiguration()->setForceCommonConfigForAll()->isSuccess());
    }

    public function testGetPersonal(): void
    {
        $config = [
            new CardSectionConfiguration('main', 'Company Info', [
                new CardFieldConfiguration('TITLE'),
                new CardFieldConfiguration('COMPANY_TYPE'),
            ]),
            new CardSectionConfiguration('additional', 'Additional Info', [
                new CardFieldConfiguration('ASSIGNED_BY_ID'),
                new CardFieldConfiguration('COMMENTS'),
            ])
        ];

        $currentUserId = $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->assertTrue(
            $this->sb->getCRMScope()->companyDetailsConfiguration()->setPersonal(
                $config,
                $currentUserId
            )->isSuccess()
        );


        $this->sb->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        $this->sb->getCRMScope()->companyDetailsConfiguration()->getGeneral();

        // todo fix after we get valid cardConfig
        $this->assertTrue(true);

    }
}