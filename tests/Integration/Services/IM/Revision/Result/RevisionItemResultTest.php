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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Revision\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Revision\Result\RevisionItemResult;
use Bitrix24\SDK\Services\IM\Revision\Service\Revision;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RevisionItemResult::class)]
class RevisionItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Revision $revisionService;

    #[\Override]
    protected function setUp(): void
    {
        $this->revisionService = Factory::getServiceBuilder()->getIMScope()->revision();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in RevisionItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->revisionService->get()
            ->getCoreResponse()->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            RevisionItemResult::class,
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in RevisionItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $revisionItemResult = $this->revisionService->get()->revision();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $revisionItemResult,
            RevisionItemResult::class,
        );
    }
}
