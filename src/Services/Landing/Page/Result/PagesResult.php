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

namespace Bitrix24\SDK\Services\Landing\Page\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class PagesResult extends AbstractResult
{
    /**
     * @return PageItemResult[]
     * @throws BaseException
     */
    public function getPages(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $page) {
            $res[] = new PageItemResult($page);
        }

        return $res;
    }
}
