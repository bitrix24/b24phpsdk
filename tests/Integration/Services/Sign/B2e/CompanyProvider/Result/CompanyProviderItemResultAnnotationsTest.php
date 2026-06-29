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

use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Result\CompanyProviderItemResult;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Service\CompanyProvider;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompanyProviderItemResult::class)]
class CompanyProviderItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private CompanyProvider $companyProviderService;

    #[\Override]
    protected function setUp(): void
    {
        $this->companyProviderService = Factory::getServiceBuilder()->getSignScope()->companyProvider();
    }

    #[Test]
    #[TestDox('testAllSystemFieldsAnnotated: all fields in CompanyProviderItemResult are annotated in phpdoc')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $this->markTestSkipped(
            'sign.b2e.company.provider.list requires a valid company CRM ID with КЭДО integration. ' .
            'Run manually with a real companyCrmId to verify annotation completeness.'
        );
    }

    #[Test]
    #[TestDox('testAllSystemFieldsHasValidTypeAnnotation: all fields in CompanyProviderItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->markTestSkipped(
            'sign.b2e.company.provider.list requires a valid company CRM ID with КЭДО integration. ' .
            'Run manually with a real companyCrmId to verify annotation types.'
        );
    }
}

