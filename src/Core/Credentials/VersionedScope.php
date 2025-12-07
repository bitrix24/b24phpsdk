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

use InvalidArgumentException;

/**
 * Container class that stores multiple Scope instances indexed by version number.
 * Versions must be unique integers starting from 1.
 */
readonly class VersionedScope
{
    /**
     * @var array<int, Scope> Map of version => Scope
     */
    private array $scopes;

    /**
     * @param array<int, Scope> $scopes Map where keys are version numbers (>= 1) and values are Scope instances
     * @throws InvalidArgumentException If array is empty, version is invalid, or value is not a Scope instance
     */
    public function __construct(array $scopes)
    {
        if ($scopes === []) {
            throw new InvalidArgumentException('At least one scope version must be provided');
        }

        foreach ($scopes as $version => $scope) {
            if (!is_int($version)) {
                throw new InvalidArgumentException(
                    sprintf('Version must be an integer, got %s', gettype($version))
                );
            }

            if ($version < 1) {
                throw new InvalidArgumentException(
                    sprintf('Version must be >= 1, got %d', $version)
                );
            }

            if (!($scope instanceof Scope)) {
                throw new InvalidArgumentException(
                    sprintf('Value must be instance of Scope, got %s', get_debug_type($scope))
                );
            }
        }

        $this->scopes = $scopes;
    }

    /**
     * Retrieves a Scope by version number.
     *
     * @param int $version Version number to retrieve
     * @return Scope The Scope instance for the given version
     * @throws InvalidArgumentException If the version does not exist
     */
    public function getScope(int $version): Scope
    {
        if (!isset($this->scopes[$version])) {
            throw new InvalidArgumentException(
                sprintf('Version %d does not exist', $version)
            );
        }

        return $this->scopes[$version];
    }

    /**
     * Returns all available version numbers in sorted order.
     *
     * @return int[] Sorted array of version numbers
     */
    public function getVersions(): array
    {
        $versions = array_keys($this->scopes);
        sort($versions);
        return $versions;
    }

    /**
     * Checks if a specific version exists in the container.
     *
     * @param int $version Version number to check
     * @return bool True if version exists, false otherwise
     */
    public function hasVersion(int $version): bool
    {
        return isset($this->scopes[$version]);
    }
}
