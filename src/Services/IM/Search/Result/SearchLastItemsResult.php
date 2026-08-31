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

namespace Bitrix24\SDK\Services\IM\Search\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SearchLastItemsResult extends AbstractResult
{
    /**
     * @return array<int, array<string, mixed>>
     * @throws BaseException
     */
    public function items(): array
    {
        return array_values(array_filter($this->getCoreResponse()->getResponseData()->getResult(), 'is_array'));
    }
}
