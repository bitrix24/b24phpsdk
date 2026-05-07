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

namespace Bitrix24\SDK\Services\Sale\PropertyRelation\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\FieldsResult;

/**
 * Class PropertyRelationFieldsResult
 * Represents the result of a property relation fields operation.
 */
class PropertyRelationFieldsResult extends FieldsResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getFieldsDescription(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return $result['propertyRelation'] ?? [];
    }
}
