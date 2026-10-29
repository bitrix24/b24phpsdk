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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormEmbedding\Service;

use Bitrix24\SDK\Core\Exceptions\VersionMismatchException;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormItemBuilder;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\EmbeddingButtonType;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\EmbeddingVariant;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\FormEmbedding;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\V3\DisposableFormTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormEmbedding::class)]
class FormEmbeddingTest extends TestCase
{
    use DisposableFormTrait;

    private FormEmbedding $embedding;

    #[\Override]
    protected function setUp(): void
    {
        $this->embedding = Factory::getServiceBuilder()->getCRMScope()->v3()->formEmbedding();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->deleteDisposableForms();
    }

    #[Test]
    public function testGetReturnsPublicLinkAndAllVariants(): void
    {
        $embedding = $this->embedding->get($this->createDisposableForm())->embedding();

        $this->assertFalse($embedding->formActive);
        $this->assertStringStartsWith('https://', (string)$embedding->publicUrl);
        $this->assertNotEmpty($embedding->inline?->code);
        $this->assertNull($embedding->inline->settings);
        $this->assertArrayHasKey('button', $embedding->click?->settings ?? []);
        $this->assertArrayHasKey('delay', $embedding->auto?->options ?? []);
    }

    #[Test]
    public function testGetVariantReturnsCodeOrLink(): void
    {
        $id = $this->createDisposableForm();

        $click = $this->embedding->getVariant($id, EmbeddingVariant::click, 'Leave a request', EmbeddingButtonType::link)->variant();
        $this->assertSame(EmbeddingVariant::click, $click->variant);
        $this->assertStringContainsString('<script', (string)$click->code);

        $auto = $this->embedding->getVariant($id, EmbeddingVariant::auto, delay: 10)->variant();
        $this->assertSame(EmbeddingVariant::auto, $auto->variant);

        $link = $this->embedding->getVariant($id, EmbeddingVariant::link)->variant();
        $this->assertSame(EmbeddingVariant::link, $link->variant);
        $this->assertStringStartsWith('https://', (string)$link->url);
    }

    #[Test]
    public function testUpdateSavesSettingsAndBumpsFormVersion(): void
    {
        $id = $this->createDisposableForm();
        $version = $this->formVersion($id);
        // updatedTime has a one-second resolution
        sleep(1);

        $this->assertTrue(
            $this->embedding->update($id, $version, ['type' => 'popup', 'position' => 'left'], ['type' => 'panel', 'delay' => 10])->isSuccess()
        );

        $embedding = $this->embedding->get($id)->embedding();
        $this->assertSame('popup', $embedding->click?->settings['type'] ?? null);
        $this->assertSame('left', $embedding->click->settings['position'] ?? null);
        $this->assertSame(10, $embedding->auto?->settings['delay'] ?? null);

        // an embedding change is a form change: the old version is now stale for both form and embedding updates
        $newVersion = $this->formVersion($id);
        $this->assertTrue($newVersion->greaterThan($version));

        try {
            $this->formService()->update($id, $version, (new FormItemBuilder())->appearance(['title' => 'Stale']));
            $this->fail('A form update with the pre-embedding version must be rejected');
        } catch (VersionMismatchException $versionMismatchException) {
            $this->assertTrue($newVersion->equalTo($versionMismatchException->getCurrentVersion()));
        }

        $this->expectException(VersionMismatchException::class);
        $this->embedding->update($id, $version, autoSettings: ['delay' => 5]);
    }
}
