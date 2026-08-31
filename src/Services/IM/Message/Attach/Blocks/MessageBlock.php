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

namespace Bitrix24\SDK\Services\IM\Message\Attach\Blocks;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;

final readonly class MessageBlock implements AttachBlockInterface
{
    private function __construct(
        private string $text,
    ) {
        if ($text === '') {
            throw new \InvalidArgumentException('Message text must not be empty');
        }
    }

    public static function text(string $text): self
    {
        return new self($text);
    }

    #[\Override]
    public function build(): array
    {
        return [
            'MESSAGE' => $this->text,
        ];
    }
}
