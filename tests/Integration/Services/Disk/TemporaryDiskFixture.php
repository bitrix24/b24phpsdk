<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Disk;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Services\Disk\File\Service\File;
use Bitrix24\SDK\Services\Disk\File\Result\FileSearchResult;
use RuntimeException;

/** Owns only objects created for the current test and never deletes pre-existing data. */
final readonly class TemporaryDiskFixture
{
    private function __construct(
        public CoreInterface $core,
        public int $rootId,
        public int $folderId,
        public int $fileId,
        public int $storageId,
        public string $query,
    ) {
    }

    public static function core(): CoreInterface
    {
        return (new CoreBuilder())->withCredentials(Credentials::createFromWebhook(
            new WebhookUrl($_ENV['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK'] ?? $_ENV['BITRIX24_WEBHOOK'])
        ))->build();
    }

    public static function create(): self
    {
        $core = self::core();
        $userId = (int)$core->call('user.current')->getResponseData()->getResult()['ID'];
        $storages = $core->call('disk.storage.getlist', [
            'filter' => ['ENTITY_TYPE' => 'user', 'ENTITY_ID' => $userId],
        ])->getResponseData()->getResult();
        if ($storages === []) {
            throw new RuntimeException('Disk integration tests need the current user personal storage.');
        }

        $query = 'sdk659' . bin2hex(random_bytes(8));
        $rootId = (int)$core->call('disk.folder.addsubfolder', [
            'id' => (int)$storages[0]['ROOT_OBJECT_ID'], 'data' => ['NAME' => $query],
        ])->getResponseData()->getResult()['ID'];
        try {
            $folderId = (int)$core->call('disk.folder.addsubfolder', [
                'id' => $rootId, 'data' => ['NAME' => $query . 'folder'],
            ])->getResponseData()->getResult()['ID'];
            $fileId = (int)$core->call('disk.folder.uploadfile', [
                'id' => $rootId, 'data' => ['NAME' => $query . '.txt'],
                'fileContent' => base64_encode('Disposable SDK search fixture'),
            ])->getResponseData()->getResult()['ID'];
        } catch (\Throwable $exception) {
            try {
                self::deleteRoot($core, $rootId);
            } catch (\Throwable $cleanupException) {
                throw new RuntimeException(sprintf(
                    'Disk fixture %d cleanup failed after %s; cleanup error: %s.',
                    $rootId, $exception::class, $cleanupException::class
                ), 0, $exception);
            }

            throw $exception;
        }

        return new self($core, $rootId, $folderId, $fileId, (int)$storages[0]['ID'], $query);
    }

    public function waitForSearch(File $file): FileSearchResult
    {
        for ($attempt = 0; $attempt < 15; ++$attempt) {
            $result = $file->search($this->query, 'all', ['FOLDER_ID' => $this->rootId]);
            $ids = array_map(static fn($item): int => $item->ID, $result->items());
            if (in_array($this->fileId, $ids, true) && in_array($this->folderId, $ids, true)) {
                return $result;
            }

            usleep(2_000_000);
        }

        throw new RuntimeException('Disk search did not index the disposable file and folder within 30 seconds.');
    }

    public function delete(): void
    {
        self::deleteRoot($this->core, $this->rootId);
    }

    private static function deleteRoot(CoreInterface $core, int $rootId): void
    {
        $result = $core->call('disk.folder.deletetree', ['id' => $rootId])->getResponseData()->getResult();
        if ($result !== [true]) {
            throw new RuntimeException(sprintf('Failed to delete disposable Disk fixture %d.', $rootId));
        }
    }
}
