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

namespace Bitrix24\SDK\Services\Lists;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

#[ApiServiceBuilderMetadata(new Scope(['lists']))]
class ListsServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Lists service for managing universal lists
     */
    public function lists(): Lists\Service\Lists
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $listsBatch = new Lists\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Lists\Service\Lists(
                new Lists\Service\Batch($listsBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Field service for managing universal list fields
     */
    public function field(): Field\Service\Field
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $fieldBatch = new Field\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Field\Service\Field(
                new Field\Service\Batch($fieldBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Section service for managing universal list sections
     */
    public function section(): Section\Service\Section
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $sectionBatch = new Section\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Section\Service\Section(
                new Section\Service\Batch($sectionBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Element service for managing universal list elements
     */
    public function element(): Element\Service\Element
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $elementBatch = new Element\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Element\Service\Element(
                new Element\Service\Batch($elementBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
