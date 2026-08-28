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

namespace Bitrix24\SDK\Services\Sign;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Service\CompanyProvider;
use Bitrix24\SDK\Services\Sign\B2e\Document\Service\Document;
use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Service\MySafeTail;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Service\PersonalTail;

#[ApiServiceBuilderMetadata(new Scope(['sign.b2e']))]
class SignServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Document service for sign.b2e.document.* methods.
     */
    public function document(): Document
    {
        $this->serviceCache[__METHOD__] ??= new Document($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Company provider service for sign.b2e.company.provider.* methods.
     */
    public function companyProvider(): CompanyProvider
    {
        $this->serviceCache[__METHOD__] ??= new CompanyProvider($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Personal tail service for sign.b2e.personal.tail method.
     */
    public function personalTail(): PersonalTail
    {
        $this->serviceCache[__METHOD__] ??= new PersonalTail($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    /**
     * My safe tail service for sign.b2e.mysafe.tail method.
     */
    public function mySafeTail(): MySafeTail
    {
        $this->serviceCache[__METHOD__] ??= new MySafeTail($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }
}
