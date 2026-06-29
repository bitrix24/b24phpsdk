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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\PersonalTail\Service;

use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Result\PersonalTailResult;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Service\PersonalTail;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonalTail::class)]
class PersonalTailTest extends TestCase
{
    #[\Override]
    protected function setUp() : void
    {
    }

    #[Test]
    #[TestDox('sign.b2e.personal.tail returns PersonalTailResult')]
    public function testTailReturnsPersonalTailResult(): void
    {
        $this->markTestSkipped(
            'sign.b2e.personal.tail requires application context (not webhook). ' .
            'Run manually with OAuth application context to verify.'
        );
    }
}
