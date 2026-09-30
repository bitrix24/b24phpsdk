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

namespace Bitrix24\SDK\Services\Telephony\Voximplant\Callback\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Telephony\Voximplant\InfoCall\Result\VoximplantInfoCallResult;

#[ApiServiceMetadata(new Scope(['telephony']))]
class Callback extends AbstractService
{
    /**
     * Starts a callback between an employee and a customer.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'voximplant.callback.start',
        'https://apidocs.bitrix24.com/api-reference/telephony/voximplant/voximplant-callback-start.html',
        'Starts a callback between an employee and a customer.'
    )]
    public function start(string $lineId, string $toNumber, string $text, ?string $voiceCode = null): VoximplantInfoCallResult
    {
        $parameters = [
            'FROM_LINE' => $lineId,
            'TO_NUMBER' => $toNumber,
            'TEXT_TO_PRONOUNCE' => $text,
        ];
        if ($voiceCode !== null) {
            $parameters['VOICE'] = $voiceCode;
        }

        return new VoximplantInfoCallResult($this->core->call('voximplant.callback.start', $parameters));
    }
}
