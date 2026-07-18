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

namespace Bitrix24\SDK\Services\Documentgenerator;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Documentgenerator\Document;
use Bitrix24\SDK\Services\Documentgenerator\Numerator;
use Bitrix24\SDK\Services\Documentgenerator\Region;
use Bitrix24\SDK\Services\Documentgenerator\Role;
use Bitrix24\SDK\Services\Documentgenerator\Template;

#[ApiServiceBuilderMetadata(new Scope(['documentgenerator']))]
class DocumentgeneratorServiceBuilder extends AbstractServiceBuilder
{
    public function document(): Document\Service\Document
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $documentBatch = new Document\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Document\Service\Document(
                new Document\Service\Batch($documentBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function template(): Template\Service\Template
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $templateBatch = new Template\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Template\Service\Template(
                new Template\Service\Batch($templateBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function numerator(): Numerator\Service\Numerator
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $numeratorBatch = new Numerator\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Numerator\Service\Numerator(
                new Numerator\Service\Batch($numeratorBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function region(): Region\Service\Region
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $regionBatch = new Region\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Region\Service\Region(
                new Region\Service\Batch($regionBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function role(): Role\Service\Role
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $roleBatch = new Role\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Role\Service\Role(
                new Role\Service\Batch($roleBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
