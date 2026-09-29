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

namespace Bitrix24\SDK\Services\Sale\PersonType\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class AddedPersonTypeBatchResult
 *
 * Result of the sale.persontype.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\PersonType\Result
 */
class AddedPersonTypeBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['personType']['id'];
    }

    public function personType(): PersonTypeItemResult
    {
        return new PersonTypeItemResult($this->getResponseData()->getResult()['personType']);
    }
}
