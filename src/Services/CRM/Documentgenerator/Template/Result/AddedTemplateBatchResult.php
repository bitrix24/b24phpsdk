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

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class AddedTemplateBatchResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result
 */
class AddedTemplateBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['template']['id'];
    }
}
