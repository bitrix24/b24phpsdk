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

namespace Bitrix24\SDK\Services\Note\File\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class FileFieldsResult extends AbstractResult
{
    /**
     * @return FileFieldItemResult[]
     * @throws BaseException
     */
    public function getFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new FileFieldItemResult($item);
        }

        return $items;
    }
}
