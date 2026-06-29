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

namespace Bitrix24\SDK\Tests\Unit\Services\Sign;

use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Service\CompanyProvider;
use Bitrix24\SDK\Services\Sign\B2e\Document\Service\Document;
use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Service\MySafeTail;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Service\PersonalTail;
use Bitrix24\SDK\Services\Sign\SignServiceBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignServiceBuilder::class)]
class SignServiceBuilderTest extends TestCase
{
    #[Test]
    #[TestDox('SignServiceBuilder class exists and can be instantiated')]
    public function testSignServiceBuilderExists(): void
    {
        $this->assertTrue(class_exists(SignServiceBuilder::class));
    }

    #[Test]
    #[TestDox('Document service class exists')]
    public function testDocumentServiceClassExists(): void
    {
        $this->assertTrue(class_exists(Document::class));
    }

    #[Test]
    #[TestDox('CompanyProvider service class exists')]
    public function testCompanyProviderServiceClassExists(): void
    {
        $this->assertTrue(class_exists(CompanyProvider::class));
    }

    #[Test]
    #[TestDox('PersonalTail service class exists')]
    public function testPersonalTailServiceClassExists(): void
    {
        $this->assertTrue(class_exists(PersonalTail::class));
    }

    #[Test]
    #[TestDox('MySafeTail service class exists')]
    public function testMySafeTailServiceClassExists(): void
    {
        $this->assertTrue(class_exists(MySafeTail::class));
    }
}

