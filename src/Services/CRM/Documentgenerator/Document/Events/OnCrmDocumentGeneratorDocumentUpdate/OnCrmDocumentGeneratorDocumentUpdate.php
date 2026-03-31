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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Events\OnCrmDocumentGeneratorDocumentUpdate;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

/**
 * @see https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/events/on-crm-document-generator-document-update.html
 */
class OnCrmDocumentGeneratorDocumentUpdate extends AbstractEventRequest
{
    public const CODE = 'ONCRMDOCUMENTGENERATORDOCUMENTUPDATE';

    public function getPayload(): OnCrmDocumentGeneratorDocumentUpdatePayload
    {
        return new OnCrmDocumentGeneratorDocumentUpdatePayload($this->eventPayload['data']);
    }
}
