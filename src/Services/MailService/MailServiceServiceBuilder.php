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

namespace Bitrix24\SDK\Services\MailService;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\MailService;

#[ApiServiceBuilderMetadata(new Scope(['mailservice']))]
class MailServiceServiceBuilder extends AbstractServiceBuilder
{
    public function mailService(): MailService\Service\MailService
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new MailService\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new MailService\Service\MailService(
                new MailService\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
