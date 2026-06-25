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

namespace Bitrix24\SDK\Services\Landing\RepoWidget\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Represents the response from landing.repowidget.debug.
 *
 * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-debug.html
 */
class RepoWidgetDebugResult extends AbstractResult
{
    /**
     * Returns true when debug mode was successfully toggled.
     *
     * @throws BaseException
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}

