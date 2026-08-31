<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\CompanyProvider\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Result\CompanyProviderItemResult;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Result\CompanyProvidersResult;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Service\CompanyProvider;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompanyProvider::class)]
class CompanyProviderTest extends TestCase
{
    private CompanyProvider $companyProviderService;

    private ?int $companyCrmId = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->companyProviderService = Fabric::getServiceBuilder(true)->getSignScope()->companyProvider();

        $value = $_ENV['SIGN_B2E_COMPANY_CRM_ID'] ?? '';
        if ($value !== '') {
            $this->companyCrmId = (int) $value;
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.company.provider.list returns CompanyProvidersResult with items array')]
    public function testListReturnsCompanyProvidersResult(): void
    {
        if ($this->companyCrmId === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_COMPANY_CRM_ID in tests/.env.local to enable this test.'
            );
        }

        $companyProvidersResult = $this->companyProviderService->list(null, $this->companyCrmId);

        self::assertInstanceOf(CompanyProvidersResult::class, $companyProvidersResult);
        self::assertIsArray($companyProvidersResult->getProviders());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.company.provider.list items are CompanyProviderItemResult instances when not empty')]
    public function testListItemsAreCompanyProviderItemResults(): void
    {
        if ($this->companyCrmId === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_COMPANY_CRM_ID in tests/.env.local to enable this test.'
            );
        }

        $providers = $this->companyProviderService->list(null, $this->companyCrmId)->getProviders();

        if ($providers === []) {
            $this->markTestSkipped('No signature providers found for this company — cannot verify item type.');
        }

        self::assertInstanceOf(CompanyProviderItemResult::class, $providers[0]);
        self::assertIsString($providers[0]->code);
        self::assertIsString($providers[0]->uid);
        self::assertIsString($providers[0]->name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('sign.b2e.company.provider.list respects limit parameter')]
    public function testListRespectsLimitParameter(): void
    {
        if ($this->companyCrmId === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_COMPANY_CRM_ID in tests/.env.local to enable this test.'
            );
        }

        $providers = $this->companyProviderService->list(null, $this->companyCrmId, null, 1, 0)->getProviders();

        self::assertLessThanOrEqual(1, count($providers));
    }
}
