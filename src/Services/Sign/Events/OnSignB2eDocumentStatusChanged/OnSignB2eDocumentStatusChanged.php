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

namespace Bitrix24\SDK\Services\Sign\Events\OnSignB2eDocumentStatusChanged;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

/**
 * Event fired when the status of a sign.b2e document changes.
 *
 * @link https://apidocs.bitrix24.com/api-reference/sign/events/on-sign-b2e-document-status-changed.html
 */
class OnSignB2eDocumentStatusChanged extends AbstractEventRequest
{
    public const CODE = 'ONSIGNB2EDOCUMENTSTATUSCHANGED';

    public function getPayload(): OnSignB2eDocumentStatusChangedPayload
    {
        return new OnSignB2eDocumentStatusChangedPayload($this->eventPayload['data']);
    }
}
