<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\PersonTypeStatus;

#[ApiServiceBuilderMetadata(new Scope(['sale']))]
class SaleServiceBuilder extends AbstractServiceBuilder
{
    public function personTypeStatus(): PersonTypeStatus
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new PersonTypeStatus(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
