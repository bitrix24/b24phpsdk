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

namespace Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $id
 * @property-read string $showUrl
 * @property-read string $downloadUrl
 */
class File extends AbstractItem
{
    public function __get($offset)
    {
        return match ($offset) {
            'id' => (int)$this->data['id'],
            default => parent::__get($offset),
        };
    }
}