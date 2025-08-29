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

namespace Bitrix24\SDK\Services\Sale\StatusLang\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class LanguagesResult - result of getting available languages
 *
 * @package Bitrix24\SDK\Services\Sale\StatusLang\Result
 */
class LanguagesResult extends AbstractResult
{
    /**
     * Get array of available languages
     *
     * @throws BaseException
     */
    public function getLanguages(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['langs'] ?? [];
    }
}
