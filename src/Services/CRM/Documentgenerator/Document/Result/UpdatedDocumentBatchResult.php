<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result;

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;

/**
 * Class UpdatedDocumentBatchResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result
 */
class UpdatedDocumentBatchResult extends UpdatedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getResponseData()->getResult();
    }
}
