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

namespace Bitrix24\SDK\Services\Telephony\FollowUp\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class FollowUpResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function followUp(): FollowUpItemResult
    {
        return new FollowUpItemResult($this->getCoreResponse()->getResponseData()->getResult()['item']);
    }
}
