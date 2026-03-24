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
 * Class TemplateResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result
 */
class TemplateResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function template(): TemplateItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        // Be tolerant to different API payload shapes
        if (!empty($result['template']) && is_array($result['template'])) {
            $result = $result['template'];
        }

        return new TemplateItemResult($result);
    }
}
