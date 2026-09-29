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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Userfieldconfig\Result;

use Bitrix24\SDK\Services\CRM\Type\Service\Type;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Service\Userfieldconfig;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserfieldConfigItemResult::class)]
class UserfieldConfigItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Userfieldconfig $userfieldConfigService;

    private Type $typeService;

    private int $scratchTypeId;

    private int $scratchFieldId;

    #[\Override]
    protected function setUp(): void
    {
        $this->userfieldConfigService = Factory::getServiceBuilder()->getCRMScope()->userfieldConfig();
        $this->typeService = Factory::getServiceBuilder()->getCRMScope()->type();

        // entityId for userfieldconfig is CRM_{id} built from the SPA type's own `id`,
        // not from `entityTypeId` (see docs for onCrmTypeUserFieldAdd, section "ENTITY_ID")
        $addedType = $this->typeService->add(sprintf('%s userfieldconfig annotations test SPA type', time()));
        $this->scratchTypeId = $addedType->getId();
        $entityId = sprintf('CRM_%d', $this->scratchTypeId);

        // uses userTypeId "enumeration" with a non-empty `enum` list so that the raw API
        // response includes the `enum` key, matching the class annotation below
        $added = $this->userfieldConfigService->add('crm', [
            'entityId' => $entityId,
            'fieldName' => sprintf('UF_%s_%d', $entityId, time()),
            'userTypeId' => 'enumeration',
            'enum' => [
                ['value' => 'A', 'def' => 'N', 'sort' => 100],
                ['value' => 'B', 'def' => 'Y', 'sort' => 200],
            ],
        ]);
        $this->scratchFieldId = $added->field()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        // deleting the SPA type also removes its custom fields
        $this->typeService->delete($this->scratchTypeId);
    }

    #[Test]
    #[TestDox('all fields in UserfieldConfigItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->userfieldConfigService->get('crm', $this->scratchFieldId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult()['field'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            UserfieldConfigItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in UserfieldConfigItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $item = $this->userfieldConfigService->get('crm', $this->scratchFieldId)->field();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $item,
            UserfieldConfigItemResult::class
        );
    }
}
