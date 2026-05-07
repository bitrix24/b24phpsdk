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

namespace Bitrix24\SDK\Services\Lists\Field\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Class AddedFieldBatchResult
 *
 * @package Bitrix24\SDK\Services\Lists\Field\Result
 */
class AddedFieldBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function getId(): string
    {
        return (string)$this->getResponseData()->getResult()[0];
    }
}
