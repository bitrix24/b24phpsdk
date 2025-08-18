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

namespace Bitrix24\SDK\Services\Main\Result;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\PortalLicenseFamily;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class ApplicationInfoResult
 *
 * Webhook credentials will return only this field:
 * SCOPE
 * LICENSE
 *
 * @property-read int|null $ID
 * @property-read string|null $CODE
 * @property-read Scope|null $SCOPE
 * @property-read int|null $VERSION
 * @property-read ApplicationStatus|null $STATUS
 * @property-read boolean|null $INSTALLED
 * @property-read boolean|null $PAYMENT_EXPIRED
 * @property-read int|null $DAYS
 * @property-read string|null $LANGUAGE_ID
 * @property-read string|null $LICENSE
 * @property-read string|null $LICENSE_TYPE
 * @property-read PortalLicenseFamily|null $LICENSE_FAMILY
 */
class ApplicationInfoItemResult extends AbstractItem
{
    /**
     * @throws UnknownScopeCodeException
     * @throws InvalidArgumentException
     */
    public function __get($offset)
    {
        switch ($offset) {
            case 'LICENSE_FAMILY':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return PortalLicenseFamily::from($this->data[$offset]);
                }

                return null;
            case 'PAYMENT_EXPIRED':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return $this->data[$offset] === 'Y';
                }

                return null;
            case 'INSTALLED':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return (bool)$this->data[$offset];
                }

                return null;
            case 'ID':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return (int)$this->data[$offset];
                }

                return null;
            case 'SCOPE':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return new Scope($this->data[$offset]);
                }
                return null;
            case 'STATUS':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return new ApplicationStatus($this->data[$offset]);
                }
                return null;
            default:
                return $this->data[$offset] ?? null;
        }
    }

    public function getStatus(): ?ApplicationStatus
    {
        return $this->STATUS;
    }
}