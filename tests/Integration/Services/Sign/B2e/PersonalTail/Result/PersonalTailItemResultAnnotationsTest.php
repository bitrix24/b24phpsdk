<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\PersonalTail\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Result\PersonalTailItemResult;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Service\PersonalTail;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonalTailItemResult::class)]
class PersonalTailItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private PersonalTail $personalTailService;

    #[\Override]
    protected function setUp(): void
    {
        $this->personalTailService = Fabric::getServiceBuilder(true)->getSignScope()->personalTail();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('testAllSystemFieldsAnnotated: all fields in PersonalTailItemResult are annotated in phpdoc and match raw API response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItems = $this->personalTailService->tail(20, 0)
            ->getCoreResponse()->getResponseData()->getResult();

        if ($rawItems === []) {
            $this->markTestSkipped(
                'No signed documents found in personal tail — cannot verify annotation completeness against live API data.'
            );
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItems[0]),
            PersonalTailItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('testAllSystemFieldsHasValidTypeAnnotation: all fields in PersonalTailItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItems = $this->personalTailService->tail(20, 0)
            ->getCoreResponse()->getResponseData()->getResult();

        if ($rawItems === []) {
            $this->markTestSkipped(
                'No signed documents found in personal tail — cannot verify type annotations against live API data.'
            );
        }

        $fieldTypesMap = [];
        foreach (array_keys($rawItems[0]) as $fieldCode) {
            $fieldTypesMap[$fieldCode] = match ($fieldCode) {
                'id' => ['type' => 'integer'],
                'title', 'file_url' => ['type' => 'string'],
                'signed_date' => ['type' => 'datetime'],
                default => throw new \RuntimeException(
                    sprintf('Unknown field «%s» in sign.b2e.personal.tail response — update the type map.', $fieldCode)
                ),
            };
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $fieldTypesMap,
            PersonalTailItemResult::class
        );
    }
}
