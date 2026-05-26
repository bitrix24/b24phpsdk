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

namespace Bitrix24\SDK\Services\Documentgenerator\Document\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class DocumentResult
 *
 * @package Bitrix24\SDK\Services\Documentgenerator\Document\Result
 */
class DocumentResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function document(): DocumentItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        if (!empty($result['document']) && is_array($result['document'])) {
            $result = $result['document'];
        }

        return new DocumentItemResult($result);
    }
}
