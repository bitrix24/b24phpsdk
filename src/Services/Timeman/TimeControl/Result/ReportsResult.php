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

namespace Bitrix24\SDK\Services\Timeman\TimeControl\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class ReportsResult extends AbstractResult
{
    public function getReport(): ReportItemResult
    {
        return new ReportItemResult($this->getCoreResponse()->getResponseData()->getResult()['report']);
    }

    public function getUser(): ReportUserItemResult
    {
        return new ReportUserItemResult($this->getCoreResponse()->getResponseData()->getResult()['user']);
    }
}
