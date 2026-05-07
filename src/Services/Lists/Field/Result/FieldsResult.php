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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class FieldsResult
 *
 * @package Bitrix24\SDK\Services\Lists\Field\Result
 */
class FieldsResult extends AbstractResult
{
    /**
     * Get array of fields
     *
     * @return FieldItemResult[]
     * @throws BaseException
     */
    public function fields(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        $fields = [];

        foreach ($result as $fieldData) {
            $fields[] = new FieldItemResult($fieldData);
        }

        return $fields;
    }
}
