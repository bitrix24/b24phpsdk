<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Placement\Service;

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\IM\Placements\ImTextareaPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangItem;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangMap;
use Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\Placement\Common\PlacementHandlerMetadata;
use Bitrix24\SDK\Services\Placement\PlacementConflictPolicy;
use Bitrix24\SDK\Services\Placement\PlacementRegistrationStatus;
use Bitrix24\SDK\Services\Placement\Service\Placement;
use Bitrix24\SDK\Services\Placement\Service\PlacementRegistrar;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(PlacementRegistrar::class)]
#[CoversClass(Placement::class)]
class PlacementRegistrarTest extends TestCase
{
    private ServiceBuilder $serviceBuilder;
    private Placement $placement;
    private PlacementRegistrar $placementRegistrar;
    private string $baseUrl;

    #[\Override]
    protected function setUp(): void
    {
        // A missing OAuth token is a failed prerequisite, never a skipped successful live check.
        $this->serviceBuilder = Factory::getServiceBuilder(true);
        $this->placement = $this->serviceBuilder->getPlacementScope()->placement();
        $this->placementRegistrar = $this->serviceBuilder->getPlacementScope()->registrar();
        $this->baseUrl = rtrim((string)$_ENV['BITRIX24_PHP_SDK_APPLICATION_DOMAIN_URL'], '/') . '/sdk446-' . bin2hex(random_bytes(12));
    }

    public function testInstallerCanCreateRepeatAndExplicitlyReplaceWithoutRemovingAnotherUrl(): void
    {
        $code = PlacementLocationCodes::IM_TEXTAREA;
        $firstUrl = $this->baseUrl . '/first';
        $secondUrl = $this->baseUrl . '/second';
        $before = $this->identities($this->rows());
        $original = $this->declaration($firstUrl, 'SDK registrar', 400);
        $changed = $this->declaration($firstUrl, 'SDK registrar updated', 500);
        try {
            $created = $this->placementRegistrar->register([$original]);
            self::assertTrue($created->isSuccess());
            self::assertSame(PlacementRegistrationStatus::created, $created->getItems()[0]->status);
            self::assertSame(PlacementRegistrationStatus::skipped, $this->placementRegistrar->register([$original])->getItems()[0]->status);

            $second = $this->placementRegistrar->register([$this->declaration($secondUrl, 'SDK second', 300)]);
            self::assertTrue($second->isSuccess());
            self::assertSame(PlacementRegistrationStatus::created, $second->getItems()[0]->status);

            $conflict = $this->placementRegistrar->register([$changed]);
            self::assertFalse($conflict->isSuccess());
            self::assertSame(PlacementRegistrationStatus::failed, $conflict->getItems()[0]->status);
            self::assertNotNull($conflict->getItems()[0]->error);
            self::assertSame(PlacementRegistrationStatus::skipped, $this->placementRegistrar->register([$original])->getItems()[0]->status);

            $updated = $this->placementRegistrar->register([$changed], PlacementConflictPolicy::replace);
            self::assertTrue($updated->isSuccess());
            self::assertSame(PlacementRegistrationStatus::updated, $updated->getItems()[0]->status);
            self::assertSame(PlacementRegistrationStatus::skipped, $this->placementRegistrar->register([$changed])->getItems()[0]->status);
            self::assertSame(PlacementRegistrationStatus::skipped, $this->placementRegistrar->register([$this->declaration($secondUrl, 'SDK second', 300)])->getItems()[0]->status);
            self::assertSame([], array_values(array_diff($before, $this->identities($this->rows()))));
        } finally {
            $this->cleanup($code, [$firstUrl, $secondUrl]);
        }
    }

    public function testExplicitGlobalUserIdDoesNotDeleteTheSameUrlForAnotherUser(): void
    {
        $code = 'PAGE_BACKGROUND_WORKER';
        if (!in_array($code, $this->placement->list()->getLocationCodes(), true)) {
            self::markTestIncomplete('The test app needs PAGE_BACKGROUND_WORKER to verify global versus user-specific removal before merge.');
        }

        $userId = (int)$this->serviceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
        self::assertGreaterThan(0, $userId);
        $url = new Url($this->baseUrl . '/background');
        $options = ['errorHandlerUrl' => $this->baseUrl . '/error'];
        $lang = ['en' => ['TITLE' => 'SDK registrar scope test']];
        try {
            self::assertTrue($this->placement->bind($code, $url, $lang, $options)->isSuccess());
            self::assertTrue($this->placement->bind($code, $url, $lang, $options, $userId)->isSuccess());
            self::assertCount(2, $this->matching($code, $url->getUrl()));
            self::assertSame(1, $this->placement->unbind($code, $url, 0)->getDeletedPlacementHandlersCount());
            $remaining = $this->matching($code, $url->getUrl());
            self::assertCount(1, $remaining);
            self::assertSame($userId, (int)$remaining[0]['userId']);

            $declaration = new PlacementHandlerMetadata($code, $url, $lang, $options, $userId);
            self::assertSame(PlacementRegistrationStatus::skipped, $this->placementRegistrar->register([$declaration])->getItems()[0]->status);
            $replacement = new PlacementHandlerMetadata($code, $url, ['en' => ['TITLE' => 'SDK updated scoped handler']], $options, $userId);
            $updated = $this->placementRegistrar->register([$replacement], PlacementConflictPolicy::replace);
            self::assertTrue($updated->isSuccess());
            self::assertSame(PlacementRegistrationStatus::updated, $updated->getItems()[0]->status);
        } finally {
            $this->cleanup($code, [$url->getUrl()]);
        }
    }

    private function declaration(string $url, string $title, int $width): PlacementHandlerMetadata
    {
        return new PlacementHandlerMetadata(
            PlacementLocationCodes::IM_TEXTAREA,
            new Url($url),
            PlacementLangMap::empty()->with(LangCodes::EN, new PlacementLangItem($title))->toArray(),
            (new ImTextareaPlacementOptions('fa-comment'))->width($width)->height(160),
        );
    }

    /** @return list<array<string, mixed>> */
    private function rows(): array
    {
        return $this->placement->get()->getCoreResponse()->getResponseData()->getResult();
    }

    /** @return list<array<string, mixed>> */
    private function matching(string $code, string $url): array
    {
        return array_values(array_filter($this->rows(), static fn (array $row): bool => $row['placement'] === $code && $row['handler'] === $url));
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<string>
     */
    private function identities(array $rows): array
    {
        return array_map(static fn (array $row): string => json_encode([$row['placement'], $row['handler'], $row['userId'] ?? 0], JSON_THROW_ON_ERROR), $rows);
    }

    /** @param list<string> $urls */
    private function cleanup(string $code, array $urls): void
    {
        $errors = [];
        foreach ($urls as $url) {
            try {
                // All registrations at these unique test URLs belong to this test,
                // including both user variants in the scope-isolation case.
                $this->placement->unbind($code, new Url($url))->getDeletedPlacementHandlersCount();
                self::assertSame([], $this->matching($code, $url));
            } catch (Throwable $exception) {
                $errors[] = $exception;
            }
        }

        if ($errors !== []) {
            throw $errors[0];
        }
    }
}
