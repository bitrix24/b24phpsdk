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

namespace Bitrix24\SDK\Core\Response\DTO;

readonly class UnsuccessfulResponseError
{
    /**
     * @param ValidationError[] $validation
     */
    public function __construct(
        public string $code,
        public string $message,
        public array $validation = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $validation = [];
        if (isset($data['validation']) && is_array($data['validation'])) {
            foreach ($data['validation'] as $validationItem) {
                $validation[] = new ValidationError(
                    (string)($validationItem['field'] ?? ''),
                    (string)($validationItem['message'] ?? '')
                );
            }
        }

        return new self(
            (string)($data['code'] ?? ''),
            (string)($data['message'] ?? ''),
            $validation
        );
    }
}
