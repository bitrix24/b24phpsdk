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

namespace Bitrix24\SDK\Services\MailService\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Mail services list result for mailservice.list.
 *
 * @see https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-list.html
 */
class MailServicesResult extends AbstractResult
{
    /**
     * Get mail service items.
     *
     * @return MailServiceItemResult[]
     * @throws BaseException
     */
    public function getMailServices(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new MailServiceItemResult($item);
        }

        return $items;
    }
}
