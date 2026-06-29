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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\PersonalTail\Result;

use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Result\PersonalTailItemResult;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Service\PersonalTail;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonalTailItemResult::class)]
class PersonalTailItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private PersonalTail $personalTailService;

    #[\Override]
    protected function setUp(): void
    {
        $this->personalTailService = Factory::getServiceBuilder()->getSignScope()->personalTail();
    }

    #[Test]
    #[TestDox('testAllSystemFieldsAnnotated: all fields in PersonalTailItemResult are annotated in phpdoc')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $this->markTestSkipped(
            'sign.b2e.personal.tail requires application context (not webhook). ' .
            'Run manually with OAuth application context to verify annotation completeness.'
        );
    }

    #[Test]
    #[TestDox('testAllSystemFieldsHasValidTypeAnnotation: all fields in PersonalTailItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->markTestSkipped(
            'sign.b2e.personal.tail requires application context (not webhook). ' .
            'Run manually with OAuth application context to verify annotation types.'
        );
    }
}
