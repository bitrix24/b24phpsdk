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

namespace Bitrix24\SDK\Services\CRM\V3\Form\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\ValidationError;
use Bitrix24\SDK\Core\Result\AbstractResult;

class FormValidationResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function isValid(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult()['valid'];
    }

    /**
     * @return ValidationError[]
     * @throws BaseException
     */
    public function getViolations(): array
    {
        $violations = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['violations'] ?? [] as $violation) {
            $violations[] = new ValidationError((string)($violation['field'] ?? ''), (string)($violation['message'] ?? ''));
        }

        return $violations;
    }
}
