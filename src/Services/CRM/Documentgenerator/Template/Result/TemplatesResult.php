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
 * Class TemplatesResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result
 */
class TemplatesResult extends AbstractResult
{
    /**
     * @return TemplateItemResult[]
     * @throws BaseException
     */
    public function getTemplates(): array
    {
        $items = [];
        $source = [];

        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['templates']) && is_array($result['templates'])) {
            $source = $result['templates'];
        } elseif (!empty($result['items']) && is_array($result['items'])) {
            $source = $result['items'];
        }

        foreach ($source as $item) {
            $items[] = new TemplateItemResult($item);
        }

        return $items;
    }
}
