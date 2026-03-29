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

use JsonException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

readonly class OaSchemaMethodReader
{
    public function __construct(
        private Filesystem $filesystem,
        private OaToSdkMethodNormalizationPolicy $normalizationPolicy,
    ) {
    }

    /**
     * @return list<string>
     *
     * @throws JsonException
     */
    public function readMethodNames(string $schemaFile): array
    {
        if (!$this->filesystem->exists($schemaFile)) {
            throw new RuntimeException(sprintf('OpenAPI schema file "%s" not found', $schemaFile));
        }

        $payload = file_get_contents($schemaFile);
        if (!is_string($payload)) {
            throw new RuntimeException(sprintf('Unable to read OpenAPI schema file "%s"', $schemaFile));
        }

        /** @var array{paths?: array<string, mixed>} $schema */
        $schema = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $paths = $schema['paths'] ?? [];

        $methods = [];
        foreach (array_keys($paths) as $pathName) {
            $normalizedMethodName = $this->normalizationPolicy->normalizeOaMethodName($pathName);
            if ($normalizedMethodName === null) {
                continue;
            }

            $methods[$normalizedMethodName] = true;
        }

        $methodNames = array_keys($methods);
        sort($methodNames);

        return $methodNames;
    }
}
