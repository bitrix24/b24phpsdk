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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class TemplateFieldsResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result
 */
class TemplateFieldsResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // API returns fields nested under templateFields key
        if (!empty($result['templateFields']) && is_array($result['templateFields'])) {
            return $result['templateFields'];
        }

        return $result;
    }
}
