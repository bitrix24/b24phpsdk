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

namespace Bitrix24\SDK\Services\IMBot\Revision\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for imbot.v2.Revision.get.
 */
class RevisionResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function revision(): RevisionItemResult
    {
        return new RevisionItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
