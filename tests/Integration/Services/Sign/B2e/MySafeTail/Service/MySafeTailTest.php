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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\MySafeTail\Service;

use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Service\MySafeTail;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySafeTail::class)]
class MySafeTailTest extends TestCase
{
    #[\Override]
    protected function setUp() : void
    {
    }

    #[Test]
    #[TestDox('sign.b2e.mysafe.tail returns MySafeTailResult')]
    public function testTailReturnsMySafeTailResult(): void
    {
        $this->markTestSkipped(
            'sign.b2e.mysafe.tail requires application context (not webhook). ' .
            'Run manually with OAuth application context to verify.'
        );
    }
}
