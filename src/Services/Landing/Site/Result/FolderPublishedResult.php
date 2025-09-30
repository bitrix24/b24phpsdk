<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Site\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class FolderPublishedResult extends AbstractResult
{
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}
