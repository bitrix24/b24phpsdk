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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result;

use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;

/**
 * Class DeletedTemplateBatchResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result
 */
class DeletedTemplateBatchResult extends DeletedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return (bool)$this->getResponseData()->getResult();
    }
}
