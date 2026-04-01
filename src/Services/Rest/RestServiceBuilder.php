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

namespace Bitrix24\SDK\Services\Rest;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Rest\Service\Scope as ScopeService;

#[ApiServiceBuilderMetadata(new Scope(['rest']))]
class RestServiceBuilder extends AbstractServiceBuilder
{
    public function scope(): ScopeService
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ScopeService($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
}
