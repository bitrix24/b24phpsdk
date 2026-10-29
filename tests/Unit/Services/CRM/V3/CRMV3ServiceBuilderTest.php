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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\Form;
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\FormEmbedding;
use Bitrix24\SDK\Services\CRM\V3\FormEmbeddingField\Service\FormEmbeddingField;
use Bitrix24\SDK\Services\CRM\V3\FormField\Service\FormField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(CRMV3ServiceBuilder::class)]
#[CoversClass(CRMServiceBuilder::class)]
class CRMV3ServiceBuilderTest extends TestCase
{
    #[Test]
    public function testAllServiceAccessorsAreTypedAndCached(): void
    {
        $crm = new CRMServiceBuilder(
            new NullCore(),
            $this->createStub(BatchOperationsInterface::class),
            $this->createStub(BulkItemsReaderInterface::class),
            new NullLogger()
        );
        $builder = $crm->v3();

        $this->assertSame($builder, $crm->v3());
        foreach (['form' => Form::class, 'formField' => FormField::class, 'formEmbedding' => FormEmbedding::class, 'formEmbeddingField' => FormEmbeddingField::class] as $accessor => $class) {
            $this->assertInstanceOf($class, $builder->$accessor());
            $this->assertSame($builder->$accessor(), $builder->$accessor());
        }

        $metadata = (new \ReflectionClass(CRMV3ServiceBuilder::class))->getAttributes(ApiServiceBuilderMetadata::class)[0]->newInstance();
        $this->assertSame(['crm'], $metadata->scope->getScopeCodes());
    }
}
