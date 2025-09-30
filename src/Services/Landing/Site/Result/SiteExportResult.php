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

namespace Bitrix24\SDK\Services\Landing\Site\Result;

use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SiteExportResult
 */
class SiteExportResult extends AbstractResult
{
    public function __construct(Response $response)
    {
        parent::__construct($response);
    }

    /**
     * Get export data (typically contains download URL or file data)
     */
    public function getExportData(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}
