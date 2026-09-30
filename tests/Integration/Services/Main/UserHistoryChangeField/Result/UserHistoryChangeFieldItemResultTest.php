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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistoryChangeField\Result;

use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result\UserHistoryChangeFieldItemResult;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Service\UserHistoryChangeField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\HistoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryChangeFieldItemResult::class)]
class UserHistoryChangeFieldItemResultTest extends TestCase
{
    use HistoryFixture;

    use CustomBitrix24Assertions;

    private const array SELECT = [
        'name',
        'type',
        'title',
        'description',
        'validationRules',
        'requiredGroups',
        'filterable',
        'sortable',
        'editable',
        'editableGroups',
        'multiple',
        'elementType',
    ];

    private UserHistoryChangeField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = $this->mainScope()->userHistoryChangeField();
    }

    #[TestDox('All descriptor response fields have PHPDoc annotations')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->service->get('data', self::SELECT)->getCoreResponse()
            ->getResponseData()->getResult()['item'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), UserHistoryChangeFieldItemResult::class);
    }

    #[TestDox('All descriptor magic getters match their annotated types')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $item = $this->service->get('data', self::SELECT)->getUserHistoryChangeField();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, UserHistoryChangeFieldItemResult::class);
    }
}
