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

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected UserfieldDocument\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch update userfield values of warehouse accounting documents
     *
     * @param array<int, array> $documents keyed by document id, each value is the «fields» payload
     *
     * @return Generator<int, UserfieldDocumentUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.userfield.document.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html',
        'Batch update userfield values of warehouse accounting documents'
    )]
    public function update(array $documents): Generator
    {
        $items = [];
        foreach ($documents as $id => $document) {
            $items[$id] = ['fields' => $document];
        }

        foreach ($this->batch->updateEntityItems('catalog.userfield.document.update', $items) as $key => $item) {
            yield $key => new UserfieldDocumentUpdatedBatchResult($item);
        }
    }
}
