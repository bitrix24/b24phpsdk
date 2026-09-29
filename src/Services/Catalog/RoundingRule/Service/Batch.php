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

namespace Bitrix24\SDK\Services\Catalog\RoundingRule\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected RoundingRule\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding price rounding rules
     *
     * @param array<int, array> $roundingRules
     *
     * @return Generator<int, RoundingRuleAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.roundingRule.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-add.html',
        'Batch adding price rounding rules'
    )]
    public function add(array $roundingRules): Generator
    {
        $items = [];
        foreach ($roundingRules as $roundingRule) {
            $items[] = ['fields' => $roundingRule];
        }

        foreach ($this->batch->addEntityItems('catalog.roundingRule.add', $items) as $key => $item) {
            yield $key => new RoundingRuleAddedBatchResult($item);
        }
    }

    /**
     * Batch delete price rounding rules
     *
     * @param int[] $roundingRuleId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.roundingRule.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-delete.html',
        'Batch delete price rounding rules'
    )]
    public function delete(array $roundingRuleId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.roundingRule.delete', $roundingRuleId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update price rounding rules
     *
     * @param array<int, array> $roundingRules keyed by rounding rule id
     *
     * @return Generator<int, RoundingRuleUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.roundingRule.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-update.html',
        'Batch update price rounding rules'
    )]
    public function update(array $roundingRules): Generator
    {
        $items = [];
        foreach ($roundingRules as $id => $roundingRule) {
            $items[$id] = ['fields' => $roundingRule];
        }

        foreach ($this->batch->updateEntityItems('catalog.roundingRule.update', $items) as $key => $item) {
            yield $key => new RoundingRuleUpdatedBatchResult($item);
        }
    }
}
