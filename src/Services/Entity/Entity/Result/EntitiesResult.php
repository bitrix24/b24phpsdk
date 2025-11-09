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

namespace Bitrix24\SDK\Services\Entity\Entity\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class EntitiesResult extends AbstractResult
{
    /**
     * @return EntityItemResult[]
     * @throws BaseException
     */
    public function getEntities(): array
    {
        $res = [];
        $entities = $this->getCoreResponse()->getResponseData()->getResult();

        if (isset($entities['ID'])) {
            return [new EntityItemResult($entities)];
        }

        foreach ($entities as $item) {
            $res[] = new EntityItemResult($item);
        }

        return $res;
    }
}