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

namespace Bitrix24\SDK\Services\IM\Notify\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class NotifySchemaResult extends AbstractResult
{
    /**
     * @return NotifySchemaItemResult[]
     * @throws BaseException
     */
    public function schema(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn(array $module): NotifySchemaItemResult => new NotifySchemaItemResult($module),
            array_values(array_filter($result, 'is_array'))
        );
    }
}
