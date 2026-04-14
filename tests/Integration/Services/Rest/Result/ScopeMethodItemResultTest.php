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

namespace Bitrix24\SDK\Tests\Integration\Services\Rest\Result;

use Bitrix24\SDK\Services\Rest\Result\ScopeMethodItemResult;
use Bitrix24\SDK\Services\Rest\Service\Scope;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScopeMethodItemResult::class)]
class ScopeMethodItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Scope $scopeService;

    #[\Override]
    protected function setUp(): void
    {
        $this->scopeService = Factory::getServiceBuilder()->getRestScope()->scope();
    }

    #[Test]
    #[TestDox('all fields in ScopeMethodItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $raw = $this->scopeService->list('rest')
            ->getCoreResponse()->getResponseData()->getResult();
        // Navigate to first leaf item: module -> controller -> method -> item
        $firstModule     = array_key_first($raw);
        $firstController = array_key_first($raw[$firstModule]);
        $firstMethod     = array_key_first($raw[$firstModule][$firstController]);
        $rawItem         = $raw[$firstModule][$firstController][$firstMethod];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ScopeMethodItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in ScopeMethodItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $items = $this->scopeService->list('rest')->getItems();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            ScopeMethodItemResult::class
        );
    }
}
