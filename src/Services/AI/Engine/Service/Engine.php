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

namespace Bitrix24\SDK\Services\AI\Engine\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\AI\Engine\EngineCategory;
use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
use Bitrix24\SDK\Services\AI\Engine\Result\EnginesResult;

#[ApiServiceMetadata(new Scope(['ai_admin']))]
class Engine extends AbstractService
{
    /**
     * Register the AI service
     *
     * @throws BaseException
     * @throws TransportException
     * @see https://apidocs.bitrix24.com/api-reference/ai/ai-engine-register.html
     */
    #[ApiEndpointMetadata(
        'ai.engine.register',
        'https://apidocs.bitrix24.com/api-reference/ai/ai-engine-register.html',
        'REST method for adding a custom service. This method registers an engine and updates it upon subsequent calls. This is not quite an embedding location, as the endpoint of the partner must adhere to strict formats.'
    )]
    public function register(
        string $name,
        string $code,
        EngineCategory $category,
        string $completionsUrl,
        EngineSettings $settings,
    ): AddedItemResult {
        return new AddedItemResult($this->core->call('ai.engine.register', [
            'name' => $name,
            'code' => $code,
            'category' => $category->value,
            'completions_url' => $completionsUrl,
            'settings' => $settings->toArray(),
        ]));
    }

    /**
     * Get the list of ai services
     *
     * @throws BaseException
     * @throws TransportException
     * @see https://apidocs.bitrix24.com/api-reference/ai/ai-engine-list.html
     */
    #[ApiEndpointMetadata(
        'ai.engine.list',
        'https://apidocs.bitrix24.com/api-reference/ai/ai-engine-list.html',
        'Get the list of ai services'
    )]
    public function list(): EnginesResult
    {
        return new EnginesResult($this->core->call('ai.engine.list'));
    }

    /**
     * Delete registered ai service
     *
     * @throws BaseException
     * @throws TransportException
     * @see https://apidocs.bitrix24.com/api-reference/ai/ai-engine-unregister.html
     */
    #[ApiEndpointMetadata(
        'ai.engine.unregister',
        'https://apidocs.bitrix24.com/api-reference/ai/ai-engine-unregister.html',
        'Delete registered ai service'
    )]
    public function unregister(string $code): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('ai.engine.unregister', [
            'code' => $code,
        ]));
    }
}