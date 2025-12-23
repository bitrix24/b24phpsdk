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

namespace Bitrix24\SDK\Core\Commands;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Symfony\Component\Uid\Uuid;

class Command
{
    public function __construct(
        private readonly string $apiMethod,
        private readonly array $parameters,
        private ?string $id = null,
        private readonly ApiVersion $version = ApiVersion::v1
    ) {
        if ($id === null) {
            $this->id = (Uuid::v7())->toRfc4122();
        }
    }

    public function getVersion(): ApiVersion
    {
        return $this->version;
    }

    public function getApiMethod(): string
    {
        return $this->apiMethod;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
