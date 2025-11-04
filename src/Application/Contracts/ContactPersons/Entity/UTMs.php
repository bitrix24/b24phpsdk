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

namespace Bitrix24\SDK\Application\Contracts\ContactPersons\Entity;

use function parse_url;
use function parse_str;

readonly class UTMs
{
    public function __construct(
        /**
         * Identifies which site sent the traffic (google, facebook, twitter, etc.)
         */
        public ?string $source = null,
        /**
         * Identifies what type of link was used (cpc, banner, email, etc.)
         */
        public ?string $medium = null,
        /**
         * Identifies a specific product promotion or strategic campaign
         */
        public ?string $campaign = null,
        /**
         * Identifies search terms used by paid search campaigns
         */
        public ?string $term = null,
        /**
         * Identifies what specifically was clicked to bring the user to the site (banner ad, text link, etc.)
         */
        public ?string $content = null,
    ) {
    }

    /**
     * Create UTMs object from URL string
     */
    public static function fromUrl(string $url): self
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query === null || $query === false) {
            return new self();
        }

        $query = strtolower($query);
        parse_str($query, $params);

        return new self(
            $params['utm_source'] ?? null,
            $params['utm_medium'] ?? null,
            $params['utm_campaign'] ?? null,
            $params['utm_term'] ?? null,
            $params['utm_content'] ?? null
        );
    }

}
