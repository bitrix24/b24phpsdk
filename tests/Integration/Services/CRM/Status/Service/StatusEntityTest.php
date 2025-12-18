<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Status\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Status\Result\StatusEntityItemResult;
use Bitrix24\SDK\Services\CRM\Status\Service\StatusEntity;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Status\Service\StatusEntity::class)]
class StatusEntityTest extends TestCase
{
    private StatusEntity $statusEntityService;
    
    private TyphoonReflector $typhoonReflector;
    
    protected function setUp(): void
    {
        $this->statusEntityService = Factory::getServiceBuilder()->getCRMScope()->statusEntity();
        $this->typhoonReflector = TyphoonReflector::build();
    }

    public function testItemAllSystemPropertiesAnnotated(): void
    {
        // get response from server with actual keys
        $propListFromApi = array_keys($this->statusEntityService->items('SOURCE')->getCoreResponse()->getResponseData()->getResult()[0]);
        // parse keys from phpdoc annotation
        $collection = $this->typhoonReflector->reflectClass(StatusEntityItemResult::class)->properties();
        $propsFromAnnotations = [];
        foreach ($collection as $meta) {
            if ($meta->isAnnotated() && !$meta->isNative()) {
                $propsFromAnnotations[] = $meta->id->name;
            }
        }

        $this->assertEquals($propListFromApi, $propsFromAnnotations,
            sprintf('in phpdocs annotations for class %s cant find fields from actual api response: %s',
                StatusEntityItemResult::class,
                implode(', ', array_values(array_diff($propListFromApi, $propsFromAnnotations)))
            ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testItems(): void
    {
        $entityId = 'SOURCE';
        
        $statusEntitiesResult = $this->statusEntityService->items($entityId);
        self::assertIsArray($statusEntitiesResult->getEntities());
    }
    
    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testTypes(): void
    {
        $statusEntityTypesResult = $this->statusEntityService->types();
        self::assertIsArray($statusEntityTypesResult->getEntityTypes());
    }

}
