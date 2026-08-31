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

namespace Bitrix24\SDK\Services\Landing\Role\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Landing\Role\Result\EnableResult;
use Bitrix24\SDK\Services\Landing\Role\Result\IsEnabledResult;
use Bitrix24\SDK\Services\Landing\Role\Result\RolesResult;
use Bitrix24\SDK\Services\Landing\Role\Result\RightsResult;
use Bitrix24\SDK\Services\Landing\Role\Result\SetAccessCodesResult;
use Bitrix24\SDK\Services\Landing\Role\Result\SetRightsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class Role extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Switches between extended and role-based permission models.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/rights/landing-role-enable.html
     *
     * @param int $mode 1 to enable role-based model, 0 to disable (enable extended model)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.role.enable',
        'https://apidocs.bitrix24.com/api-reference/landing/rights/landing-role-enable.html',
        'Method switches between extended and role-based permission models.'
    )]
    public function enable(int $mode): EnableResult
    {
        return new EnableResult(
            $this->core->call('landing.role.enable', [
                'mode' => $mode
            ])
        );
    }

    /**
     * Determines which permission model is currently enabled.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/rights/landing-role-is-enabled.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.role.isEnabled',
        'https://apidocs.bitrix24.com/api-reference/landing/rights/landing-role-is-enabled.html',
        'Method determines which permission model is currently enabled (extended or role-based).'
    )]
    public function isEnabled(): IsEnabledResult
    {
        return new IsEnabledResult(
            $this->core->call('landing.role.isEnabled', [])
        );
    }

    /**
     * Retrieves a list of all roles.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-get-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.role.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-get-list.html',
        'Method retrieves a list of all roles with their identifiers and names.'
    )]
    public function getList(): RolesResult
    {
        return new RolesResult(
            $this->core->call('landing.role.getList', [])
        );
    }

    /**
     * Retrieves a list of sites with permissions for a specific role.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-get-rights.html
     *
     * @param int $id Role identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.role.getRights',
        'https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-get-rights.html',
        'Method retrieves a list of sites with permissions for a specific role.'
    )]
    public function getRights(int $id): RightsResult
    {
        return new RightsResult(
            $this->core->call('landing.role.getRights', [
                'id' => $id
            ])
        );
    }

    /**
     * Sets access codes for a role.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-set-access-codes.html
     *
     * @param int $id Role identifier
     * @param array $codes Array of access codes (SG{id}, U{id}, DR{id}, UA, G{id})
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.role.setAccessCodes',
        'https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-set-access-codes.html',
        'Method sets access codes for a role that will apply to this role.'
    )]
    public function setAccessCodes(int $id, array $codes): SetAccessCodesResult
    {
        return new SetAccessCodesResult(
            $this->core->call('landing.role.setAccessCodes', [
                'id' => $id,
                'codes' => $codes
            ])
        );
    }

    /**
     * Sets role permissions for site lists.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-set-rights.html
     *
     * @param int $id Role identifier
     * @param array $rights Array of sites for rights binding (site ID => array of permissions)
     * @param array|null $additional Additional role rights (menu24, create)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.role.setRights',
        'https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-set-rights.html',
        'Method sets role permissions for site lists with additional role rights.'
    )]
    public function setRights(int $id, array $rights, ?array $additional = null): SetRightsResult
    {
        $params = [
            'id' => $id,
            'rights' => $rights
        ];

        if ($additional !== null) {
            $params['additional'] = $additional;
        }

        return new SetRightsResult(
            $this->core->call('landing.role.setRights', $params)
        );
    }
}
