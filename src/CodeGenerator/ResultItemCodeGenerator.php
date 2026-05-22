<?php

declare(strict_types=1);

namespace Bitrix24\SDK\CodeGenerator;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayload;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Payload\ResultItemPayloadField;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;
use Carbon\CarbonImmutable;

readonly class ResultItemCodeGenerator
{
    private string $templatePath;

    public function __construct(
        private ?ResultItemPhpDocTypeResolver $typeResolver = null,
        ?string $templatePath = null,
    ) {
        $this->templatePath = $templatePath ?? __DIR__ . '/Templates/ResultItem.tpl.php';
    }

    /**
     * @param list<ResultFieldDescriptor> $fields
     */
    public function generate(string $namespace, string $className, array $fields, string $sourceName = ''): string
    {
        $typeResolver = $this->typeResolver ?? new ResultItemPhpDocTypeResolver();

        usort(
            $fields,
            static fn(ResultFieldDescriptor $left, ResultFieldDescriptor $right): int => $left->name <=> $right->name
        );

        return $this->generateFromPayload(
            $namespace,
            $className,
            new ResultItemPayload(
                method: 'legacy',
                object: 'result-item',
                generatedFrom: [],
                fields: array_map(
                    static fn(ResultFieldDescriptor $field): ResultItemPayloadField => new ResultItemPayloadField(
                        code: $field->name,
                        sourceType: $field->type,
                        phpdocType: $typeResolver->resolve($field),
                        format: $field->format,
                        required: $field->required,
                        nullable: $field->nullable,
                        source: $field->source ?? ($sourceName !== '' ? $sourceName : 'legacy'),
                        description: $field->description,
                        notes: null,
                    ),
                    $fields,
                ),
                sections: [],
            ),
            $sourceName,
        );
    }

    public function generateFromPayload(
        string $namespace,
        string $className,
        ResultItemPayload $payload,
        string $sourceName = 'payload',
    ): string {
        $phpDocFields = $this->buildPropertyDefinitionsFromPayload($payload);
        $needsCarbon = false;
        foreach ($payload->fields as $field) {
            $needsCarbon = $needsCarbon || str_contains($field->phpdocType, CarbonImmutable::class);
        }

        ob_start();
        extract([
            'namespace' => $namespace,
            'className' => $className,
            'phpDocFields' => $phpDocFields,
            'needsCarbon' => $needsCarbon,
            'sourceName' => $sourceName,
        ]);
        include $this->templatePath;

        return (string) ob_get_clean();
    }

    /**
     * @return list<array{
     *     name: string,
     *     phpType: string,
     *     required: bool,
     *     nullable: bool,
     *     description: string|null
     * }>
     */
    public function buildPropertyDefinitionsFromPayload(ResultItemPayload $payload): array
    {
        $definitions = array_map(
            static fn(ResultItemPayloadField $field): array => [
                'name' => $field->code,
                'phpType' => str_replace(CarbonImmutable::class, 'CarbonImmutable', $field->phpdocType),
                'required' => $field->required,
                'nullable' => $field->nullable,
                'description' => $field->description,
            ],
            $payload->fields,
        );

        return $definitions;
    }
}
