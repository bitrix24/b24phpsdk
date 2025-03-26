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
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\AI\Engine\EngineSettings;

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
        string $category,
        string $completionsUrl,
        EngineSettings $settings,
    ) {
        return $this->core->call('ai.engine.register', [
            'name' => $name,
            'code' => $code,
            'category' => $category,
            'completions_url' => $completionsUrl,
            'settings' => $settings->toArray(),
        ]);
    }
}