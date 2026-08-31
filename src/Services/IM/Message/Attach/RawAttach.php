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

namespace Bitrix24\SDK\Services\IM\Message\Attach;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachPayloadInterface;

final readonly class RawAttach implements AttachPayloadInterface
{
    /**
     * @param array<array-key, mixed> $payload
     */
    private function __construct(
        private array $payload,
    ) {
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }

    #[\Override]
    public function build(): array
    {
        return $this->payload;
    }
}
