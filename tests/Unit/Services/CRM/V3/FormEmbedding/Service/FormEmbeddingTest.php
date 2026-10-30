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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormEmbedding\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result\EmbeddingVariantItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\EmbeddingButtonType;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\EmbeddingVariant;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\FormEmbedding;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(FormEmbedding::class)]
class FormEmbeddingTest extends TestCase
{
    private const array CLICK_SETTINGS = ['type' => 'panel', 'position' => 'right', 'vertical' => 'bottom', 'button' => ['use' => false]];

    private const array EMBEDDING = [
        'formActive' => true,
        'resourcesReady' => true,
        'publicUrl' => 'https://example.bitrix24.site/crm_form_abc/',
        'inline' => ['code' => '<script data-b24-form="inline/4/abc"></script>', 'previewUrl' => 'https://example.test/pub/form/4/?preview=inline', 'settings' => null, 'options' => null],
        'click' => ['code' => '<script data-b24-form="click/4/abc"></script>', 'previewUrl' => 'https://example.test/pub/form/4/?preview=click', 'settings' => self::CLICK_SETTINGS, 'options' => ['type' => ['popup', 'panel']]],
        'auto' => ['code' => '<script data-b24-form="auto/4/abc"></script>', 'previewUrl' => 'https://example.test/pub/form/4/?preview=auto', 'settings' => ['type' => 'popup', 'delay' => 5], 'options' => ['delay' => [3, 5, 7]]],
    ];

    #[Test]
    public function testGetReturnsAllVariantsAsTypedItems(): void
    {
        $embedding = $this->service('crm.form.embedding.get', ['id' => 4], ['item' => self::EMBEDDING])->get(4)->embedding();

        $this->assertTrue($embedding->formActive);
        $this->assertTrue($embedding->resourcesReady);
        $this->assertSame('https://example.bitrix24.site/crm_form_abc/', $embedding->publicUrl);
        $this->assertInstanceOf(EmbeddingVariantItemResult::class, $embedding->inline);
        $this->assertNull($embedding->inline->settings);
        $this->assertSame(self::CLICK_SETTINGS, $embedding->click?->settings);
        $this->assertSame(['delay' => [3, 5, 7]], $embedding->auto?->options);
        $this->assertStringContainsString('click/4', (string)$embedding->click->code);
    }

    #[Test]
    public function testGetVariantSendsClickButtonOptionsAndMapsCode(): void
    {
        $variant = $this->service(
            'crm.form.embedding.get',
            ['id' => 4, 'variant' => 'click', 'buttonText' => 'Go now', 'buttonType' => 'link'],
            ['item' => ['variant' => 'click', 'code' => '<script id="bx24_form_link"></script>']]
        )->getVariant(4, EmbeddingVariant::click, 'Go now', EmbeddingButtonType::link)->variant();

        $this->assertSame(EmbeddingVariant::click, $variant->variant);
        $this->assertSame('<script id="bx24_form_link"></script>', $variant->code);
        $this->assertNull($variant->url);
    }

    #[Test]
    public function testGetVariantSendsAutoDelay(): void
    {
        $this->service(
            'crm.form.embedding.get',
            ['id' => 4, 'variant' => 'auto', 'delay' => 10],
            ['item' => ['variant' => 'auto', 'code' => '<script></script>']]
        )->getVariant(4, EmbeddingVariant::auto, delay: 10);
    }

    #[Test]
    public function testGetVariantMapsLinkUrl(): void
    {
        $variant = $this->service(
            'crm.form.embedding.get',
            ['id' => 4, 'variant' => 'link'],
            ['item' => ['variant' => 'link', 'url' => 'https://example.bitrix24.site/crm_form_abc/']]
        )->getVariant(4, EmbeddingVariant::link)->variant();

        $this->assertSame(EmbeddingVariant::link, $variant->variant);
        $this->assertSame('https://example.bitrix24.site/crm_form_abc/', $variant->url);
        $this->assertNull($variant->code);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    #[Test]
    #[DataProvider('variantOptionMismatchCases')]
    public function testGetVariantRejectsOptionsOfOtherVariants(EmbeddingVariant $variant, array $arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FormEmbedding($this->createStub(CoreInterface::class), new NullLogger()))->getVariant(4, $variant, ...$arguments);
    }

    public static function variantOptionMismatchCases(): \Generator
    {
        yield 'button text for inline' => [EmbeddingVariant::inline, ['buttonText' => 'Go']];
        yield 'button type for auto' => [EmbeddingVariant::auto, ['buttonType' => EmbeddingButtonType::button]];
        yield 'delay for click' => [EmbeddingVariant::click, ['delay' => 5]];
        yield 'delay for link' => [EmbeddingVariant::link, ['delay' => 5]];
    }

    #[Test]
    public function testUpdateWrapsSettingsPerVariantAndSendsVersion(): void
    {
        $this->assertTrue(
            $this->service(
                'crm.form.embedding.update',
                [
                    'id' => 4,
                    'updatedTime' => '2026-10-29T14:16:55+03:00',
                    'fields' => [
                        'click' => ['settings' => ['type' => 'popup', 'button' => ['text' => null]]],
                        'auto' => ['settings' => ['delay' => 10]],
                    ],
                ],
                ['result' => true]
            )->update(
                4,
                CarbonImmutable::parse('2026-10-29T14:16:55+03:00'),
                ['type' => 'popup', 'button' => ['text' => null]],
                ['delay' => 10]
            )->isSuccess()
        );
    }

    #[Test]
    public function testUpdateSendsOnlyPassedVariant(): void
    {
        $this->service(
            'crm.form.embedding.update',
            ['id' => 4, 'updatedTime' => '2026-10-29T14:16:55+03:00', 'fields' => ['auto' => ['settings' => ['type' => 'panel']]]],
            ['result' => true]
        )->update(4, CarbonImmutable::parse('2026-10-29T14:16:55+03:00'), autoSettings: ['type' => 'panel']);
    }

    #[Test]
    public function testUpdateRequiresSettings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FormEmbedding($this->createStub(CoreInterface::class), new NullLogger()))->update(4, CarbonImmutable::now());
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(FormEmbedding::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['crm'], $metadata->scope->getScopeCodes());
        $base = 'https://apidocs.bitrix24.com/api-reference/crm/form/placement/';
        foreach (['get' => 'get', 'getVariant' => 'get', 'update' => 'update'] as $method => $action) {
            $endpoint = (new \ReflectionMethod(FormEmbedding::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('crm.form.embedding.' . $action, $endpoint->name);
            $this->assertSame($base . 'crm-form-embedding-' . $action . '.html', $endpoint->documentationUrl);
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): FormEmbedding
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new FormEmbedding($core, new NullLogger());
    }
}
