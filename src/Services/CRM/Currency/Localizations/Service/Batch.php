<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Currency\Localizations\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Currency\Localizations\Result\LocalizationItemResult;
use Bitrix24\SDK\Services\CRM\Currency\Localizations;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected Localizations\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch set localizations
     *
     * @param array <int, array{
     *   id?: string,
     *   localizations?: array <string, array>
     *   }> $localizations
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.currency.localizations.set',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-set.html',
        'Batch set localizations'
    )]
    public function set(array $localizations): Generator
    {
        foreach ($this->batch->addEntityItems('crm.currency.localizations.set', $localizations) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch delete localizations
     *
     * @param array <int, array{
     *      id: string,
     *      lids: string[]
     * }> $id
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.currency.localizations.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-delete.html',
        'Batch delete localizations'
    )]
    public function delete(array $id): Generator
    {
        foreach ($this->batch->deleteLocalizationItems('crm.currency.localizations.delete', $id) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
