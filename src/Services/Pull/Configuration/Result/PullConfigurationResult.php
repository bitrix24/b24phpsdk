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

namespace Bitrix24\SDK\Services\Pull\Configuration\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

class PullConfigurationResult extends AbstractResult
{
    /**
     * Preserves deployment-specific configuration, including optional jwt and clientId.
     * @return array<string, mixed>
     * @throws BaseException
     */
    public function getConfiguration(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}
