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

namespace Bitrix24\SDK\Services\IM\User\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Services\IM\User\UserStatusType;

/**
 * @property-read UserStatusType|null $STATUS
 */
class UserStatusItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return mixed
     */
    public function __get($offset)
    {
        if ($offset === 'STATUS' && !empty($this->data[$offset])) {
            return UserStatusType::from($this->data[$offset]);
        }

        return parent::__get($offset);
    }
}
