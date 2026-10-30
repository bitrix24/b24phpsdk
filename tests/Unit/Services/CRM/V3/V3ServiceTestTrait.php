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

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

/**
 * Core double that expects one REST v3 call and answers with the given result.
 */
trait V3ServiceTestTrait
{
    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $result
     */
    private function v3Core(string $method, array $parameters, array $result): CoreInterface
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return $core;
    }

    /**
     * @param class-string             $serviceClass
     * @param array<string, string>    $methods sdk method => REST method
     */
    private function assertRestV3Endpoints(string $serviceClass, array $methods, string $documentationBase): void
    {
        $metadata = (new \ReflectionClass($serviceClass))->getAttributes(\Bitrix24\SDK\Attributes\ApiServiceMetadata::class)[0]->newInstance();
        self::assertSame(['crm'], $metadata->scope->getScopeCodes());
        foreach ($methods as $method => $restMethod) {
            $endpoint = (new \ReflectionMethod($serviceClass, $method))->getAttributes(\Bitrix24\SDK\Attributes\ApiEndpointMetadata::class)[0]->newInstance();
            self::assertSame($restMethod, $endpoint->name);
            self::assertSame($documentationBase . str_replace('.', '-', $restMethod) . '.html', $endpoint->documentationUrl);
            self::assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }
}
