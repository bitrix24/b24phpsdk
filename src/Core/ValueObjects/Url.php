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

namespace Bitrix24\SDK\Core\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

final readonly class Url
{
    private string $url;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('URL %s is invalid', $url));
        }

        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
