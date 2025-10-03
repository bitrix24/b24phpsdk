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

namespace Bitrix24\SDK\Services\Landing\Template\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class TemplateRefsResult extends AbstractResult
{
    /**
     * Returns array where keys are included area identifiers and values are page identifiers
     *
     * @throws BaseException
     */
    public function getRefs(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}
