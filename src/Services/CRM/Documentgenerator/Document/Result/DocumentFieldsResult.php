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
 * Class DocumentFieldsResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result
 */
class DocumentFieldsResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // API returns fields nested under documentFields key
        if (!empty($result['documentFields']) && is_array($result['documentFields'])) {
            return $result['documentFields'];
        }

        return $result;
    }
}
