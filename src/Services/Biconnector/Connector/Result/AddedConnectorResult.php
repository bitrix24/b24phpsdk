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

namespace Bitrix24\SDK\Services\Biconnector\Connector\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemResult;

/**
 * Class AddedConnectorResult
 */
class AddedConnectorResult extends AddedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getId(): int
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['connector']['id'])) {
            return (int)$result['connector']['id'];
        }

        if (!empty($result['id'])) {
            return (int)$result['id'];
        }

        return (int)$result;
    }
}
