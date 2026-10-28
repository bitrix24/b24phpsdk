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

namespace Bitrix24\SDK\Tests\Unit\Services\Note;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\File\Service\File;
use Bitrix24\SDK\Services\Note\FileField\Service\FileField;
use Bitrix24\SDK\Services\Note\NoteServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(NoteServiceBuilder::class)]
class NoteServiceBuilderTest extends TestCase
{
    #[Test]
    public function testAllServiceAccessorsAreTypedAndCached(): void
    {
        $builder = new NoteServiceBuilder(new NullCore(), $this->createStub(BatchOperationsInterface::class), $this->createStub(BulkItemsReaderInterface::class), new NullLogger());

        foreach (['fileField' => FileField::class, 'file' => File::class, 'collection' => Collection::class, 'document' => Document::class] as $accessor => $class) {
            $this->assertInstanceOf($class, $builder->$accessor());
            $this->assertSame($builder->$accessor(), $builder->$accessor());
        }

        $this->assertNotSame($builder->file(), $builder->fileField());
        $metadata = (new \ReflectionClass(NoteServiceBuilder::class))->getAttributes(ApiServiceBuilderMetadata::class)[0]->newInstance();
        $this->assertSame(['note'], $metadata->scope->getScopeCodes());
    }
}
