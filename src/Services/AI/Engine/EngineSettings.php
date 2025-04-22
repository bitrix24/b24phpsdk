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

namespace Bitrix24\SDK\Services\AI\Engine;

readonly class EngineSettings
{
    public function __construct(
        public string $codeAlias,
        public ModelContextType $modelContextType = ModelContextType::token,
        public int $modelContextLimit = 16000
    ) {
    }


    public static function fromArray(array $data): self
    {
        return new self(
            $data['code_alias'],
            ModelContextType::from($data['model_context_type']),
            $data['model_context_limit']
        );
    }
    public function toArray(): array
    {
        return [
            'code_alias' => $this->codeAlias,
            'model_context_type' => $this->modelContextType->value,
            'model_context_limit' => $this->modelContextLimit,
        ];
    }
}