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

namespace Bitrix24\SDK\Tests\Unit\Services\Rest\Service;

use Bitrix24\SDK\Services\Rest\Result\ScopeMethodsResult;
use Bitrix24\SDK\Services\Rest\Service\Scope;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Scope::class)]
class ScopeTest extends TestCase
{
    private Scope $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Scope(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testListReturnsScopeMethodsResult(): void
    {
        $this->assertInstanceOf(ScopeMethodsResult::class, $this->service->list());
    }

    #[Test]
    public function testListWithFilterModuleReturnsScopeMethodsResult(): void
    {
        $this->assertInstanceOf(ScopeMethodsResult::class, $this->service->list('rest'));
    }
}
