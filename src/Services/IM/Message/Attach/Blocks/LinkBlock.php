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

final class LinkBlock implements AttachBlockInterface
{
    private const string TARGET_URL = 'url';

    private const string TARGET_USER = 'user';

    private const string TARGET_CHAT = 'chat';

    private const string TARGET_NETWORK = 'network';

    private ?string $link = null;

    private ?string $targetMode = null;

    private ?string $name = null;

    private ?string $description = null;

    private ?string $html = null;

    private ?string $preview = null;

    private ?int $width = null;

    private ?int $height = null;

    private ?int $userId = null;

    private ?int $chatId = null;

    private ?string $networkId = null;

    public static function url(string $link): self
    {
        $block = new self();
        $block->setTargetMode(self::TARGET_URL);

        $block->link = self::requireNonEmptyString($link, 'LINK');

        return $block;
    }

    public static function user(int $userId, ?string $name = null): self
    {
        $block = new self();
        $block->setTargetMode(self::TARGET_USER);
        $block->userId($userId);

        if ($name !== null) {
            $block->name($name);
        }

        return $block;
    }

    public static function chat(int $chatId, ?string $name = null): self
    {
        $block = new self();
        $block->setTargetMode(self::TARGET_CHAT);
        $block->chatId($chatId);

        if ($name !== null) {
            $block->name($name);
        }

        return $block;
    }

    public static function network(string $networkId, ?string $name = null): self
    {
        $block = new self();
        $block->setTargetMode(self::TARGET_NETWORK);
        $block->networkId($networkId);

        if ($name !== null) {
            $block->name($name);
        }

        return $block;
    }

    public function name(string $name): self
    {
        $this->name = self::requireNonEmptyString($name, 'NAME');

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = self::requireNonEmptyString($description, 'DESC');

        return $this;
    }

    public function html(string $html): self
    {
        $this->html = self::requireNonEmptyString($html, 'HTML');

        return $this;
    }

    public function preview(string $url): self
    {
        $this->preview = self::requireNonEmptyString($url, 'PREVIEW');

        return $this;
    }

    public function width(int $width): self
    {
        if ($width <= 0) {
            throw new \InvalidArgumentException('LINK width must be greater than zero');
        }

        $this->width = $width;

        return $this;
    }

    public function height(int $height): self
    {
        if ($height <= 0) {
            throw new \InvalidArgumentException('LINK height must be greater than zero');
        }

        $this->height = $height;

        return $this;
    }

    public function userId(int $userId): self
    {
        $this->setTargetMode(self::TARGET_USER);

        if ($userId <= 0) {
            throw new \InvalidArgumentException('USER_ID must be greater than zero');
        }

        $this->userId = $userId;

        return $this;
    }

    public function chatId(int $chatId): self
    {
        $this->setTargetMode(self::TARGET_CHAT);

        if ($chatId <= 0) {
            throw new \InvalidArgumentException('CHAT_ID must be greater than zero');
        }

        $this->chatId = $chatId;

        return $this;
    }

    public function networkId(string $networkId): self
    {
        $this->setTargetMode(self::TARGET_NETWORK);
        $this->networkId = self::requireNonEmptyString($networkId, 'NETWORK_ID');

        return $this;
    }

    #[\Override]
    public function build(): array
    {
        $fields = [];

        match ($this->targetMode) {
            self::TARGET_URL => $fields['LINK'] = $this->link,
            self::TARGET_USER => $fields['USER_ID'] = $this->userId,
            self::TARGET_CHAT => $fields['CHAT_ID'] = $this->chatId,
            self::TARGET_NETWORK => $fields['NETWORK_ID'] = $this->networkId,
            default => throw new \LogicException('Link target mode is not configured'),
        };

        $this->appendField($fields, 'NAME', $this->name);
        $this->appendField($fields, 'DESC', $this->description);
        $this->appendField($fields, 'HTML', $this->html);
        $this->appendField($fields, 'PREVIEW', $this->preview);
        $this->appendField($fields, 'WIDTH', $this->width);
        $this->appendField($fields, 'HEIGHT', $this->height);

        return [
            'LINK' => array_filter(
                $fields,
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

    /**
     * @param array<string, int|string> $fields
     */
    private function appendField(array &$fields, string $key, int|string|null $value): void
    {
        if ($value !== null) {
            $fields[$key] = $value;
        }
    }

    private function setTargetMode(string $targetMode): void
    {
        if ($this->targetMode !== null && $this->targetMode !== $targetMode) {
            throw new \InvalidArgumentException('LinkBlock can only use one target mode');
        }

        $this->targetMode = $targetMode;
    }
}
