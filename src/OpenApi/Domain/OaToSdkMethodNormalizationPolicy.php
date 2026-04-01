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

namespace Bitrix24\SDK\OpenApi\Domain;

readonly class OaToSdkMethodNormalizationPolicy
{
    private const DOCUMENTATION_BASE_URL = 'https://apidocs.bitrix24.com/api-reference/rest-v3';
    private const NO_SCOPE = '–';

    /**
     * @var array<string, string>
     */
    private array $methodAliases;

    /**
     * @var array<string, string>
     */
    private array $ignoredMethods;

    /**
     * @var array<string, string>
     */
    private array $scopeAliases;

    public function __construct()
    {
        $this->methodAliases = [
            'rest.documentation.openapi' => 'documentation',
        ];
        $this->ignoredMethods = [];
        $this->scopeAliases = [
            'tasks' => 'task',
        ];
    }

    public function normalizeOaMethodName(string $rawPath): ?string
    {
        $normalizedMethodName = ltrim($rawPath, '/');
        if ($normalizedMethodName === '') {
            return null;
        }

        if (array_key_exists($normalizedMethodName, $this->methodAliases)) {
            $normalizedMethodName = $this->methodAliases[$normalizedMethodName];
        }

        if (array_key_exists($normalizedMethodName, $this->ignoredMethods)) {
            return null;
        }

        return $normalizedMethodName;
    }

    public function deriveScope(string $methodName): string
    {
        if (!str_contains($methodName, '.')) {
            return self::NO_SCOPE;
        }

        $scope = strstr($methodName, '.', true);
        if ($scope === false || $scope === '') {
            return self::NO_SCOPE;
        }

        return $scope;
    }

    public function isScopeCompatible(string $methodName, string $sdkScope): bool
    {
        $endpointScope = $this->deriveScope($methodName);
        if ($endpointScope === self::NO_SCOPE) {
            return $sdkScope === '';
        }

        return $this->normalizeScope($endpointScope) === $this->normalizeScope($sdkScope);
    }

    public function getScopeMismatchDiagnostic(string $methodName, string $sdkScope): ?string
    {
        if ($this->isScopeCompatible($methodName, $sdkScope)) {
            return null;
        }

        return sprintf(
            'Method "%s" has endpoint scope "%s" but service scope "%s"',
            $methodName,
            $this->deriveScope($methodName),
            $sdkScope === '' ? self::NO_SCOPE : $sdkScope
        );
    }

    public function buildDocumentationUrl(string $methodName): string
    {
        $normalizedMethodName = str_replace('.', '-', $methodName);
        $scope = $this->deriveScope($methodName);

        if ($scope === self::NO_SCOPE) {
            return sprintf('%s/%s.html', self::DOCUMENTATION_BASE_URL, $normalizedMethodName);
        }

        return sprintf('%s/%s/%s.html', self::DOCUMENTATION_BASE_URL, $scope, $normalizedMethodName);
    }

    /**
     * @return array<string, string>
     */
    public function getIgnoredMethods(): array
    {
        return $this->ignoredMethods;
    }

    private function normalizeScope(string $scope): string
    {
        if ($scope === '' || $scope === self::NO_SCOPE) {
            return self::NO_SCOPE;
        }

        return $this->scopeAliases[$scope] ?? $scope;
    }
}
