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

namespace Bitrix24\SDK\Services\Biconnector\Connector\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ConnectorResult
 */
class ConnectorResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function connector(): ConnectorItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['connector']) && is_array($result['connector'])) {
            $result = $result['connector'];
        }

        return new ConnectorItemResult($result);
    }
}
