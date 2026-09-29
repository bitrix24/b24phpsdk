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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\CompanyProvider\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Result\CompanyProviderItemResult;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Service\CompanyProvider;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompanyProviderItemResult::class)]
class CompanyProviderItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

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
    #[TestDox('testAllSystemFieldsAnnotated: all fields in CompanyProviderItemResult are annotated in phpdoc and match raw API response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        if ($this->companyCrmId === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_COMPANY_CRM_ID in tests/.env.local to enable this test.'
            );
        }

        $rawItems = $this->companyProviderService->list(null, $this->companyCrmId)
            ->getCoreResponse()->getResponseData()->getResult();

        if ($rawItems === []) {
            $this->markTestSkipped(
                'No signature providers found for this company — cannot verify annotation completeness against live API data.'
            );
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItems[0]),
            CompanyProviderItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('testAllSystemFieldsHasValidTypeAnnotation: all fields in CompanyProviderItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        if ($this->companyCrmId === null) {
            $this->markTestSkipped(
                'Set SIGN_B2E_COMPANY_CRM_ID in tests/.env.local to enable this test.'
            );
        }

        $rawItems = $this->companyProviderService->list(null, $this->companyCrmId)
            ->getCoreResponse()->getResponseData()->getResult();

        if ($rawItems === []) {
            $this->markTestSkipped(
                'No signature providers found for this company — cannot verify type annotations against live API data.'
            );
        }

        $fieldTypesMap = [];
        foreach (array_keys($rawItems[0]) as $fieldCode) {
            $fieldTypesMap[$fieldCode] = match ($fieldCode) {
                'code', 'uid', 'name' => ['type' => 'string'],
                'date', 'expires' => ['type' => 'datetime'],
                default => throw new \RuntimeException(
                    sprintf('Unknown field «%s» in sign.b2e.company.provider.list response — update the type map.', $fieldCode)
                ),
            };
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fieldTypesMap,
            CompanyProviderItemResult::class
        );
    }
}
