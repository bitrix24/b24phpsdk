<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Provider;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OfficialDocumentationResultFieldProvider
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return list<ResultFieldDescriptor>|null
     */
    public function provide(string $documentationUrl, string $objectName): ?array
    {
        foreach ($this->getCandidateUrls($documentationUrl) as $candidateUrl) {
            $html = $this->httpClient->request('GET', $candidateUrl)->getContent();
            $fields = $this->extractFields($html, $objectName);
            if ($fields !== null) {
                return $fields;
            }

            $embeddedHtml = $this->extractDiplodocStateHtml($html);
            if ($embeddedHtml === null) {
                continue;
            }

            $fields = $this->extractFields($embeddedHtml, $objectName);
            if ($fields !== null) {
                return $fields;
            }
        }

        return null;
    }

    private function buildXpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        @$document->loadHTML($html);

        return new DOMXPath($document);
    }

    /**
     * @return list<ResultFieldDescriptor>|null
     */
    private function extractFields(string $html, string $objectName): ?array
    {
        $xpath = $this->buildXpath($html);
        $heading = $this->findObjectHeading($xpath, $objectName);
        if ($heading === null) {
            return null;
        }

        $table = $this->findNextTable($heading);
        if (!$table instanceof DOMElement) {
            return null;
        }

        $fields = [];
        foreach ($xpath->query('.//tbody/tr', $table) ?: [] as $row) {
            if (!$row instanceof DOMElement) {
                continue;
            }

            $cells = $xpath->query('./td', $row);
            if ($cells === false || $cells->length < 2) {
                continue;
            }

            [$name, $rawType, $description] = $this->extractRowParts($xpath, $cells);
            if ($name === '' || $rawType === '') {
                continue;
            }

            if (strtolower($name) === 'name' && strtolower($rawType) === 'type') {
                continue;
            }

            [$type, $format] = $this->normalizeType($rawType);
            $fields[] = new ResultFieldDescriptor(
                $name,
                $type,
                $format,
                $this->isNullable($description),
                $description,
                'documentation'
            );
        }

        return $fields === [] ? null : $fields;
    }

    /**
     * @param \DOMNodeList<DOMElement> $cells
     * @return array{0: string, 1: string, 2: string}
     */
    private function extractRowParts(DOMXPath $xpath, \DOMNodeList $cells): array
    {
        $nameCell = $cells->item(0);
        $name = trim((string) $xpath->evaluate('string(.//strong[1])', $nameCell));
        if ($name === '') {
            $name = $this->firstNonEmptyLine((string) $nameCell?->textContent);
        }

        $rawType = trim((string) $xpath->evaluate('string(.//code[1])', $nameCell));
        if ($rawType === '' && $cells->length >= 2) {
            $rawType = trim((string) $cells->item(1)?->textContent);
        }

        $descriptionCellIndex = $cells->length >= 3 ? 2 : 1;
        $description = trim((string) $cells->item($descriptionCellIndex)?->textContent);

        return [$name, $rawType, $description];
    }

    private function findObjectHeading(DOMXPath $xpath, string $objectName): ?DOMElement
    {
        $normalizedObjectName = strtolower(sprintf('Object %s', $objectName));

        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') ?: [] as $heading) {
            if (!$heading instanceof DOMElement) {
                continue;
            }

            $headingText = strtolower(trim(preg_replace('/\s+/', ' ', (string) $heading->textContent) ?? ''));
            if ($headingText !== '' && str_contains($headingText, $normalizedObjectName)) {
                return $heading;
            }
        }

        return null;
    }

    private function findNextTable(DOMNode $node): ?DOMNode
    {
        $cursor = $node->nextSibling;
        while ($cursor !== null) {
            if ($cursor instanceof DOMElement && strtolower($cursor->tagName) === 'table') {
                return $cursor;
            }

            $cursor = $cursor->nextSibling;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function normalizeType(string $rawType): array
    {
        $normalized = strtolower(trim($rawType));

        return match ($normalized) {
            'datetime' => ['string', 'date-time'],
            'date' => ['string', 'date'],
            'integer', 'int' => ['integer', null],
            'boolean', 'bool' => ['boolean', null],
            'array' => ['array', null],
            'object' => ['object', null],
            default => ['string', null],
        };
    }

    private function isNullable(string $description): bool
    {
        $normalizedDescription = strtolower($description);

        return str_contains($normalizedDescription, 'null')
            || str_contains($normalizedDescription, 'not specified');
    }

    private function extractDiplodocStateHtml(string $html): ?string
    {
        $xpath = $this->buildXpath($html);
        $scriptNodes = $xpath->query('//script[@id="diplodoc-state"]');
        if ($scriptNodes === false || $scriptNodes->length === 0) {
            return null;
        }

        $payload = trim((string) $scriptNodes->item(0)?->textContent);
        if ($payload === '') {
            return null;
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return null;
        }

        $embeddedHtml = $decoded['data']['html'] ?? null;
        if (!is_string($embeddedHtml) || $embeddedHtml === '') {
            return null;
        }

        return html_entity_decode($embeddedHtml, ENT_QUOTES | ENT_HTML5);
    }

    private function firstNonEmptyLine(string $value): string
    {
        foreach (preg_split('/\R/u', $value) ?: [] as $line) {
            $normalizedLine = trim($line);
            if ($normalizedLine !== '') {
                return $normalizedLine;
            }
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function getCandidateUrls(string $documentationUrl): array
    {
        $candidates = [trim($documentationUrl)];

        if (str_starts_with($documentationUrl, 'https://apidocs.bitrix24.ru/')) {
            $candidates[] = str_replace('https://apidocs.bitrix24.ru/', 'https://apidocs.bitrix24.com/', $documentationUrl);
        }

        return array_values(array_unique(array_filter($candidates, static fn(string $url): bool => $url !== '')));
    }
}
