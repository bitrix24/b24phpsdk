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

namespace Bitrix24\SDK\Attributes\Services;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Symfony\Component\Filesystem\Filesystem;
use Typhoon\Reflection\TyphoonReflector;

use function Typhoon\Type\stringify;

readonly class AttributesParser
{
    public function __construct(
        private TyphoonReflector $typhoonReflector,
        private Filesystem $filesystem,
    ) {
    }

    /**
     * @param class-string[] $sdkClassNames
     * @param non-empty-string $sdkBaseDir
     * @return list<SupportedInSdkApiMethod>
     *
     * @throws UnknownScopeCodeException
     */
    public function getSupportedInSdkApiMethods(array $sdkClassNames, string $sdkBaseDir, ?Scope $scope = null): array
    {
        $supportedInSdkMethods = [];
        foreach ($sdkClassNames as $className) {
            $reflectionServiceClass = new ReflectionClass($className);
            $apiServiceAttribute = $reflectionServiceClass->getAttributes(ApiServiceMetadata::class);
            if ($apiServiceAttribute === []) {
                continue;
            }
            $typhoonClassMeta = $this->typhoonReflector->reflectClass($className);
            $apiServiceAttribute = $apiServiceAttribute[0];
            /**
             * @var ApiServiceMetadata $apiServiceAttrInstance
             */
            $apiServiceAttrInstance = $apiServiceAttribute->newInstance();
            // process api service
            $serviceMethods = $reflectionServiceClass->getMethods();
            foreach ($serviceMethods as $method) {
                $attributes = $method->getAttributes(ApiEndpointMetadata::class);
                foreach ($attributes as $attribute) {
                    /**
                     * @var ApiEndpointMetadata $instance
                     */
                    $instance = $attribute->newInstance();
                    $sdkReturnTypeDeclaration = null;
                    if ($method->getReturnType() !== null) {
                        $sdkReturnTypeDeclaration = stringify(
                            $typhoonClassMeta->methods()[$method->getName()]->returnType()
                        );
                    }
                    $returnTypeMetadata = $this->normalizeSdkReturnTypeMetadata(
                        $method->getReturnType(),
                        $sdkBaseDir,
                        $sdkReturnTypeDeclaration
                    );

                    $supportedInSdkMethods[] = new SupportedInSdkApiMethod(
                        sdkScope: $apiServiceAttrInstance->scope->getScopeCodes() === [] ? '' : $apiServiceAttrInstance->scope->getScopeCodes()[0],
                        name: $instance->name,
                        documentationUrl: $instance->documentationUrl,
                        description: $instance->description,
                        isDeprecated: $instance->isDeprecated,
                        deprecationMessage: $instance->deprecationMessage,
                        sdkMethodName: $method->getName(),
                        sdkMethodFileName: substr(
                            $this->filesystem->makePathRelative($method->getFileName(), $sdkBaseDir),
                            0,
                            -1
                        ),
                        sdkMethodFileStartLine: $method->getStartLine(),
                        sdkMethodFileEndLine: $method->getEndLine(),
                        sdkClassName: $className,
                        apiVersion: $instance->apiVersion,
                        sdkReturnTypeClass: $returnTypeMetadata['sdkReturnTypeClass'],
                        sdkReturnTypeFileName: $returnTypeMetadata['sdkReturnTypeFileName'],
                        sdkReturnTypeDeclaration: $returnTypeMetadata['sdkReturnTypeDeclaration'],
                    );
                }
            }
        }

        if ($scope instanceof Scope) {
            $supportedInSdkMethods = array_values(array_filter(
                $supportedInSdkMethods,
                static function (SupportedInSdkApiMethod $supportedInSdkApiMethod) use ($scope): bool {
                    if ($supportedInSdkApiMethod->sdkScope === '') {
                        return false;
                    }

                    return $scope->contains($supportedInSdkApiMethod->sdkScope);
                }
            ));
        }

        return $supportedInSdkMethods;
    }

    /**
     * @return array{
     *     sdkReturnTypeClass: ?string,
     *     sdkReturnTypeFileName: ?string,
     *     sdkReturnTypeDeclaration: ?string
     * }
     */
    private function normalizeSdkReturnTypeMetadata(
        ?ReflectionType $reflectionType,
        string $sdkBaseDir,
        ?string $sdkReturnTypeDeclaration = null
    ): array
    {
        if (!$reflectionType instanceof ReflectionType) {
            return [
                'sdkReturnTypeClass' => null,
                'sdkReturnTypeFileName' => null,
                'sdkReturnTypeDeclaration' => null,
            ];
        }

        $sdkReturnTypeDeclaration ??= $this->stringifyReflectionType($reflectionType);
        $sdkReturnTypeDeclaration = $this->normalizeTypeDeclarationString($sdkReturnTypeDeclaration);
        $sdkReturnTypeClass = $this->resolveSdkReturnTypeClass($reflectionType);

        if ($sdkReturnTypeClass === null) {
            return [
                'sdkReturnTypeClass' => null,
                'sdkReturnTypeFileName' => null,
                'sdkReturnTypeDeclaration' => $sdkReturnTypeDeclaration,
            ];
        }

        $reflectionReturnType = new ReflectionClass($sdkReturnTypeClass);
        $sdkReturnTypeFileName = null;
        if (is_string($reflectionReturnType->getFileName())) {
            $sdkReturnTypeFileName = substr(
                $this->filesystem->makePathRelative($reflectionReturnType->getFileName(), $sdkBaseDir),
                0,
                -1
            );
        }

        return [
            'sdkReturnTypeClass' => $sdkReturnTypeClass,
            'sdkReturnTypeFileName' => $sdkReturnTypeFileName,
            'sdkReturnTypeDeclaration' => $sdkReturnTypeDeclaration,
        ];
    }

    private function stringifyReflectionType(ReflectionType $reflectionType): string
    {
        if ($reflectionType instanceof ReflectionNamedType) {
            $typeName = $reflectionType->getName();

            if ($reflectionType->allowsNull() && $typeName !== 'mixed' && $typeName !== 'null') {
                return $typeName . '|null';
            }

            return $typeName;
        }

        if ($reflectionType instanceof ReflectionUnionType) {
            return implode('|', array_map(
                $this->stringifyReflectionType(...),
                $reflectionType->getTypes()
            ));
        }

        if ($reflectionType instanceof ReflectionIntersectionType) {
            return implode('&', array_map(
                $this->stringifyReflectionType(...),
                $reflectionType->getTypes()
            ));
        }

        return (string)$reflectionType;
    }

    private function resolveSdkReturnTypeClass(ReflectionType $reflectionType): ?string
    {
        if (!$reflectionType instanceof ReflectionNamedType) {
            return null;
        }

        $typeName = $reflectionType->getName();

        if (!$this->isExistingPhpType($typeName)) {
            return null;
        }

        return $typeName;
    }

    private function isExistingPhpType(string $typeName): bool
    {
        return class_exists($typeName) || interface_exists($typeName) || enum_exists($typeName);
    }

    private function normalizeTypeDeclarationString(string $sdkReturnTypeDeclaration): string
    {
        if (!str_contains($sdkReturnTypeDeclaration, '|')) {
            return $sdkReturnTypeDeclaration;
        }

        $types = explode('|', $sdkReturnTypeDeclaration);
        if (!in_array('null', $types, true) || count($types) < 2) {
            return $sdkReturnTypeDeclaration;
        }

        $types = array_values(array_filter(
            $types,
            static fn (string $type): bool => $type !== 'null'
        ));
        $types[] = 'null';

        return implode('|', $types);
    }

    /**
     * @param class-string[] $sdkClassNames
     * @return array<string, mixed>
     */
    public function getSupportedInSdkBatchMethods(array $sdkClassNames): array
    {
        $supportedInSdkMethods = [];
        foreach ($sdkClassNames as $className) {
            $reflectionServiceClass = new ReflectionClass($className);
            $apiServiceAttribute = $reflectionServiceClass->getAttributes(ApiBatchServiceMetadata::class);
            if ($apiServiceAttribute === []) {
                continue;
            }
            //try to get type information from phpdoc annotations
            $typhoonClassMeta = $this->typhoonReflector->reflectClass($className);
            /**
             * @var ApiBatchServiceMetadata $apiServiceAttrInstance
             */
            $apiServiceAttribute = $apiServiceAttribute[0];
            $apiServiceAttrInstance = $apiServiceAttribute->newInstance();
            // process api service
            $serviceMethods = $reflectionServiceClass->getMethods();
            foreach ($serviceMethods as $method) {
                $attributes = $method->getAttributes(ApiBatchMethodMetadata::class);
                foreach ($attributes as $attribute) {
                    /**
                     * @var ApiBatchMethodMetadata $instance
                     */
                    $instance = $attribute->newInstance();
                    $sdkReturnTypeTyphoon = null;
                    if ($method->getReturnType() !== null) {
                        // get return type from phpdoc annotation
                        $sdkReturnTypeTyphoon = stringify(
                            $typhoonClassMeta->methods()[$method->getName()]->returnType()
                        );
                    }

                    $supportedInSdkMethods[$instance->name][] = [
                        'sdk_scope' => $apiServiceAttrInstance->scope->getScopeCodes()[0],
                        'name' => $instance->name,
                        'documentation_url' => $instance->documentationUrl,
                        'description' => $instance->description,
                        'is_deprecated' => $instance->isDeprecated,
                        'deprecation_message' => $instance->deprecationMessage,
                        'sdk_method_name' => $method->getName(),
                        'sdk_method_file_name' => $method->getFileName(),
                        'sdk_method_file_start_line' => $method->getStartLine(),
                        'sdk_method_file_end_line' => $method->getEndLine(),
                        'sdk_method_return_type_typhoon' => $sdkReturnTypeTyphoon,
                        'sdk_class_name' => $className,
                    ];
                }
            }
        }
        return $supportedInSdkMethods;
    }
}
