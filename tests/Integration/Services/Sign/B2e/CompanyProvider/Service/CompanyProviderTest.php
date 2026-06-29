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

use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Service\CompanyProvider;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompanyProvider::class)]
class CompanyProviderTest extends TestCase
{
    #[\Override]
    protected function setUp() : void
    {
    }

    #[Test]
    #[TestDox('sign.b2e.company.provider.list returns CompanyProvidersResult')]
    public function testListReturnsCompanyProvidersResult(): void
    {
        $this->markTestSkipped(
            'sign.b2e.company.provider.list requires a valid company CRM ID with КЭДО integration. ' .
            'Run manually with a real companyCrmId to verify.'
        );
    }
}
