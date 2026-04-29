<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ApiEndpointDocumentationUrlResolver
{
    /** @var array<string, string>|null */
    private ?array $cache = null;

    public function __construct(
        private readonly Finder $finder,
        private readonly string $servicesDir = 'src/Services',
    ) {
    }

    public function resolve(string $methodName): ?string
    {
        if ($this->cache === null) {
            $this->cache = $this->buildCache();
        }

        return $this->cache[$methodName] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private function buildCache(): array
    {
        $mapping = [];

        $files = (clone $this->finder)
            ->files()
            ->in($this->servicesDir)
            ->name('*.php');

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $className = 'Bitrix24\\SDK\\Services\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (!class_exists($className)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($className);

            foreach ($reflectionClass->getMethods() as $method) {
                foreach ($method->getAttributes(ApiEndpointMetadata::class) as $attribute) {
                    /** @var ApiEndpointMetadata $metadata */
                    $metadata = $attribute->newInstance();
                    if ($metadata->documentationUrl !== '') {
                        $mapping[$metadata->name] = $metadata->documentationUrl;
                    }
                }
            }
        }

        return $mapping;
    }
}
