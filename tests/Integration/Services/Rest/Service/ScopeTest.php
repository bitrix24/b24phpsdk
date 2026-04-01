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

namespace Bitrix24\SDK\Tests\Integration\Services\Rest\Service;

use Bitrix24\SDK\Services\Rest\Service\Scope;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    private Scope $scopeService;

    #[\Override]
    protected function setUp(): void
    {
        $this->scopeService = Factory::getServiceBuilder()->getRestScope()->scope();
    }

    #[Test]
    public function testListReturnsItems(): void
    {
        $items = $this->scopeService->list('rest')->getItems();
        $this->assertNotEmpty($items);
    }

    #[Test]
    public function testListWithFilterModuleReturnsOnlyMatchingItems(): void
    {
        $items = $this->scopeService->list('rest')->getItems();
        foreach ($items as $item) {
            // scope is either the module name ('rest') for wildcard entries, or starts with 'rest.'
            $this->assertStringStartsWith('rest', $item->scope);
        }
    }
}
