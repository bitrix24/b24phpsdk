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

namespace Bitrix24\SDK\Services\IMBot\Command\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for imbot.v2.Command.register / imbot.v2.Command.update.
 *
 * Response shape: { result: { command: {...} } }
 */
class CommandResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function command(): CommandItemResult
    {
        return new CommandItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['command']
        );
    }
}
