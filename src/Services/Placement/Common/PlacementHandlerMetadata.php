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

namespace Bitrix24\SDK\Services\Placement\Common;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Placement\PlacementOptionsInterface;

final readonly class PlacementHandlerMetadata
{
    public string $placementCode;
    public Url $handlerUrl;
    public array $options;
    public ?int $b24UserId;

    /** @param array<string, array{TITLE: string, DESCRIPTION?: string, GROUP_NAME?: string}> $lang */
    public function __construct(string $placementCode, string|Url $handlerUrl, public array $lang = [], PlacementOptionsInterface|array $options = [], ?int $b24UserId = null)
    {
        if (trim($placementCode) === '') {
            throw new InvalidArgumentException('Placement code must not be empty.');
        }

        $url = $handlerUrl instanceof Url ? $handlerUrl : new Url($handlerUrl);
        if (!in_array(strtolower((string) parse_url($url->getUrl(), PHP_URL_SCHEME)), ['http', 'https'], true)) {
            throw new InvalidArgumentException('Placement handler URL must use HTTP or HTTPS.');
        }

        if ($b24UserId !== null && $b24UserId <= 0) {
            throw new InvalidArgumentException('Placement user ID must be positive or null for global registration.');
        }

        $this->placementCode = strtoupper($placementCode);
        $this->handlerUrl = $url;
        $this->options = $options instanceof PlacementOptionsInterface ? $options->build() : $options;
        $this->b24UserId = $b24UserId;
    }
}
