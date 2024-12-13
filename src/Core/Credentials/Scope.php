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

namespace Bitrix24\SDK\Core\Credentials;

use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;

class Scope
{
    /**
     * @var string[]
     */
    protected static array $availableScope = [
        'ai_admin',
        'appform',
        'baas',
        'biconnector',
        'bizproc',
        'calendar',
        'calendarmobile',
        'call',
        'cashbox',
        'catalog',
        'catalogmobile',
        'configuration.import',
        'contact_center',
        'crm',
        'delivery',
        'department',
        'disk',
        'documentgenerator',
        'entity',
        'faceid',
        'forum',
        'humanresources.hcmlink',
        'iblock',
        'im',
        'im.import',
        'imbot',
        'imconnector',
        'imopenlines',
        'intranet',
        'landing',
        'landing_cloud',
        'lists',
        'log',
        'mailservice',
        'messageservice',
        'mobile',
        'notifications',
        'pay_system',
        'placement',
        'pull',
        'pull_channel',
        'rating',
        'rpa',
        'sale',
        'salescenter',
        'sign.b2e',
        'smile',
        'socialnetwork',
        'sonet_group',
        'task',
        'tasks',
        'tasks_extended',
        'tasksmobile',
        'telephony',
        'timeman',
        'user',
        'user.userfield',
        'user_basic',
        'user_brief',
        'userconsent',
        'userfieldconfig',
    ];

    protected array $currentScope = [];

    /**
     * Scope constructor.
     *
     *
     * @throws UnknownScopeCodeException
     */
    public function __construct(array $scope = [])
    {
        $scope = array_unique(array_map(strtolower(...), $scope));
        sort($scope);
        if (count($scope) === 1 && $scope[0] === '') {
            $scope = [];
        } else {
            foreach ($scope as $item) {
                if (!in_array($item, $this::$availableScope, true)) {
                    throw new UnknownScopeCodeException(sprintf('unknown application scope code - %s', $item));
                }
            }
        }

        $this->currentScope = $scope;
    }

    public function equal(self $scope): bool
    {
        return $this->currentScope === $scope->getScopeCodes();
    }

    /**
     * @param non-empty-string $scopeCode
     * @throws UnknownScopeCodeException
     */
    public function contains(string $scopeCode): bool
    {
        $scopeCode = strtolower($scopeCode);
        if (!in_array($scopeCode, $this::$availableScope, true)) {
            throw new UnknownScopeCodeException(sprintf('unknown application scope code - %s', $scopeCode));
        }

        return in_array($scopeCode, $this->currentScope, true);
    }

    public function getScopeCodes(): array
    {
        return $this->currentScope;
    }

    /**
     * @return non-empty-string[]
     */
    public static function getAvailableScopeCodes(): array
    {
        return self::$availableScope;
    }

    /**
     * @throws UnknownScopeCodeException
     */
    public static function initFromString(string $scope): self
    {
        return new self(str_replace(' ', '', explode(',', $scope)));
    }
}