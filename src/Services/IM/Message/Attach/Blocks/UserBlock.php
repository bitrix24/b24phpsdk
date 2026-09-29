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

use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachAvatarType;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;

final class UserBlock implements AttachBlockInterface
{
    private ?string $name = null;

    private ?int $userId = null;

    private ?int $chatId = null;

    private ?int $botId = null;

    private ?string $networkId = null;

    private ?string $avatar = null;

    private ?string $link = null;

    private ?AttachAvatarType $avatarType = null;

    public static function name(string $name): self
    {
        $block = new self();
        $block->name = self::requireNonEmptyString($name, 'NAME');

        return $block;
    }

    public function userId(int $userId): self
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('USER_ID must be greater than zero');
        }

        $this->userId = $userId;

        return $this;
    }

    public function chatId(int $chatId): self
    {
        if ($chatId <= 0) {
            throw new \InvalidArgumentException('CHAT_ID must be greater than zero');
        }

        $this->chatId = $chatId;

        return $this;
    }

    public function botId(int $botId): self
    {
        if ($botId <= 0) {
            throw new \InvalidArgumentException('BOT_ID must be greater than zero');
        }

        $this->botId = $botId;

        return $this;
    }

    public function networkId(string $networkId): self
    {
        $this->networkId = self::requireNonEmptyString($networkId, 'NETWORK_ID');

        return $this;
    }

    public function avatar(string $url): self
    {
        $this->avatar = self::requireNonEmptyString($url, 'AVATAR');

        return $this;
    }

    public function link(string $url): self
    {
        $this->link = self::requireNonEmptyString($url, 'LINK');

        return $this;
    }

    public function avatarType(AttachAvatarType $attachAvatarType): self
    {
        $this->avatarType = $attachAvatarType;

        return $this;
    }

    public function avatarTypeUser(): self
    {
        return $this->avatarType(AttachAvatarType::user);
    }

    public function avatarTypeChat(): self
    {
        return $this->avatarType(AttachAvatarType::chat);
    }

    public function avatarTypeBot(): self
    {
        return $this->avatarType(AttachAvatarType::bot);
    }

    #[\Override]
    public function build(): array
    {
        return [
            'USER' => array_filter(
                [
                    'NAME' => $this->name,
                    'USER_ID' => $this->userId,
                    'CHAT_ID' => $this->chatId,
                    'BOT_ID' => $this->botId,
                    'NETWORK_ID' => $this->networkId,
                    'AVATAR' => $this->avatar,
                    'LINK' => $this->link,
                    'AVATAR_TYPE' => $this->avatarType?->value,
                ],
                static fn (mixed $value): bool => $value !== null
            ),
        ];
    }

    private static function requireNonEmptyString(string $value, string $fieldName): string
    {
        if ($value === '') {
            throw new \InvalidArgumentException(sprintf('%s must not be empty', $fieldName));
        }

        return $value;
    }
}
