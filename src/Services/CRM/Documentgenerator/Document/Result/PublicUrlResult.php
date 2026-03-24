<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PublicUrlResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result
 */
class PublicUrlResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getPublicUrl(): ?string
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['publicUrl'])) {
            return (string)$result['publicUrl'];
        }

        return null;
    }

    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}
