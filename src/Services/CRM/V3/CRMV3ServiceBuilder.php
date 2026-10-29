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

    public function formEmbedding(): FormEmbedding\Service\FormEmbedding
    {
        $this->serviceCache[__METHOD__] ??= new FormEmbedding\Service\FormEmbedding($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formEmbeddingField(): FormEmbeddingField\Service\FormEmbeddingField
    {
        $this->serviceCache[__METHOD__] ??= new FormEmbeddingField\Service\FormEmbeddingField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formConfiguration(): FormConfiguration\Service\FormConfiguration
    {
        $this->serviceCache[__METHOD__] ??= new FormConfiguration\Service\FormConfiguration($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formConfigurationField(): FormConfigurationField\Service\FormConfigurationField
    {
        $this->serviceCache[__METHOD__] ??= new FormConfigurationField\Service\FormConfigurationField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formEntityField(): FormEntityField\Service\FormEntityField
    {
        $this->serviceCache[__METHOD__] ??= new FormEntityField\Service\FormEntityField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formEntityFieldField(): FormEntityFieldField\Service\FormEntityFieldField
    {
        $this->serviceCache[__METHOD__] ??= new FormEntityFieldField\Service\FormEntityFieldField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formScenario(): FormScenario\Service\FormScenario
    {
        $this->serviceCache[__METHOD__] ??= new FormScenario\Service\FormScenario($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formScenarioField(): FormScenarioField\Service\FormScenarioField
    {
        $this->serviceCache[__METHOD__] ??= new FormScenarioField\Service\FormScenarioField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formScenarioCategory(): FormScenarioCategory\Service\FormScenarioCategory
    {
        $this->serviceCache[__METHOD__] ??= new FormScenarioCategory\Service\FormScenarioCategory($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function formScenarioCategoryField(): FormScenarioCategoryField\Service\FormScenarioCategoryField
    {
        $this->serviceCache[__METHOD__] ??= new FormScenarioCategoryField\Service\FormScenarioCategoryField($this->core, $this->log);

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
