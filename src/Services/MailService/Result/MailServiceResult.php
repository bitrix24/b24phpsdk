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

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Single mail service result for mailservice.get.
 *
 * @see https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-get.html
 */
class MailServiceResult extends AbstractResult
{
    /**
     * Get mail service item.
     */
    public function mailService(): MailServiceItemResult
    {
        return new MailServiceItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
