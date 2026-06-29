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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\MySafeTail\Result;

use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Result\MySafeTailItemResult;
use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Service\MySafeTail;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySafeTailItemResult::class)]
class MySafeTailItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private MySafeTail $mySafeTailService;

    #[\Override]
    protected function setUp(): void
    {
        $this->mySafeTailService = Factory::getServiceBuilder()->getSignScope()->mySafeTail();
    }

    #[Test]
    #[TestDox('testAllSystemFieldsAnnotated: all fields in MySafeTailItemResult are annotated in phpdoc')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $this->markTestSkipped(
            'sign.b2e.mysafe.tail requires application context (not webhook). ' .
            'Run manually with OAuth application context to verify annotation completeness.'
        );
    }

    #[Test]
    #[TestDox('testAllSystemFieldsHasValidTypeAnnotation: all fields in MySafeTailItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->markTestSkipped(
            'sign.b2e.mysafe.tail requires application context (not webhook). ' .
            'Run manually with OAuth application context to verify annotation types.'
        );
    }
}

