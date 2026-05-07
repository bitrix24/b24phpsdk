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

namespace Bitrix24\SDK\Services\IMOpenLines\Config\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class GetResult
 *
 * Result class for imopenlines.config.get
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Config\Result
 */
class GetResult extends AbstractResult
{
    /**
     * Return an open line
     */
    public function config(): OptionItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
<<<<<<< HEAD

=======
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        return new OptionItemResult($result);
    }
}
