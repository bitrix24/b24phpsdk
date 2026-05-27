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
}
