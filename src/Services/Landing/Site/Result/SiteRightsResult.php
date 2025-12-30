<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Site\Result;

use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SiteRightsResult
 */
class SiteRightsResult extends AbstractResult
{
    public function __construct(Response $response)
    {
        parent::__construct($response);
    }

    /**
     * Get access rights for the current user
     *
     * @return string[] Array of access rights: 'denied', 'read', 'edit', 'sett', 'public', 'delete'
     */
    public function getRights(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return is_array($result) ? $result : [];
    }

    /**
     * Check if user has specific right
     */
    public function hasRight(string $right): bool
    {
        return in_array($right, $this->getRights(), true);
    }

    /**
     * Check if user can read the site
     */
    public function canRead(): bool
    {
        return $this->hasRight('read');
    }

    /**
     * Check if user can edit the site content
     */
    public function canEdit(): bool
    {
        return $this->hasRight('edit');
    }

    /**
     * Check if user can change site settings
     */
    public function canChangeSett(): bool
    {
        return $this->hasRight('sett');
    }

    /**
     * Check if user can publish the site
     */
    public function canPublish(): bool
    {
        return $this->hasRight('public');
    }

    /**
     * Check if user can delete the site
     */
    public function canDelete(): bool
    {
        return $this->hasRight('delete');
    }

    /**
     * Check if user has no access (denied)
     */
    public function isDenied(): bool
    {
        return $this->hasRight('denied');
    }
}
