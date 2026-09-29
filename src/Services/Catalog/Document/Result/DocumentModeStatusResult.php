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

namespace Bitrix24\SDK\Services\Catalog\Document\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DocumentModeStatusResult extends AbstractResult
{
    /**
     * Returns true if warehouse accounting mode is enabled ('Y'), false otherwise ('N')
     *
     * @throws BaseException
     */
    public function isEnabled(): bool
    {
        return $this->getCoreResponse()->getResponseData()->getResult()[0] === 'Y';
    }
}
