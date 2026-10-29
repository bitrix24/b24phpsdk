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

namespace Bitrix24\SDK\Services\CRM\V3;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

/**
 * REST API v3 services of the crm scope; REST v1 services stay in CRMServiceBuilder.
 */
#[ApiServiceBuilderMetadata(new Scope(['crm']))]
class CRMV3ServiceBuilder extends AbstractServiceBuilder
{
    public function form(): Form\Service\Form
    {
        $this->serviceCache[__METHOD__] ??= new Form\Service\Form($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formField(): FormField\Service\FormField
    {
        $this->serviceCache[__METHOD__] ??= new FormField\Service\FormField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formSiteButton(): FormSiteButton\Service\FormSiteButton
    {
        $this->serviceCache[__METHOD__] ??= new FormSiteButton\Service\FormSiteButton($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formSiteButtonField(): FormSiteButtonField\Service\FormSiteButtonField
    {
        $this->serviceCache[__METHOD__] ??= new FormSiteButtonField\Service\FormSiteButtonField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formOpenLine(): FormOpenLine\Service\FormOpenLine
    {
        $this->serviceCache[__METHOD__] ??= new FormOpenLine\Service\FormOpenLine($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formOpenLineField(): FormOpenLineField\Service\FormOpenLineField
    {
        $this->serviceCache[__METHOD__] ??= new FormOpenLineField\Service\FormOpenLineField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }
}
