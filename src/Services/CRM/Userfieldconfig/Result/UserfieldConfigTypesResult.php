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

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UserfieldConfigTypesResult extends AbstractResult
{
    /**
     * @return array<string, UserfieldConfigTypeItemResult> keyed by user field type code
     * @throws BaseException
     */
    public function getTypes(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['types'] as $typeCode => $item) {
            $items[$typeCode] = new UserfieldConfigTypeItemResult($item);
        }

        return $items;
    }
}
