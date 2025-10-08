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
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PropertyRelationsResult
 * Represents the result of property relations list operation.
 */
class PropertyRelationsResult extends AbstractResult
{
    /**
     * @return PropertyRelationItemResult[]
     * @throws BaseException
     */
    public function getPropertyRelations(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['propertyRelations'] as $propertyRelation) {
            $res[] = new PropertyRelationItemResult($propertyRelation);
        }

        return $res;
    }
}
