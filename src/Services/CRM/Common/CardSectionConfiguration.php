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

namespace Bitrix24\SDK\Services\CRM\Common;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

readonly class CardSectionConfiguration
{
    public string $name;
    public string $title;
    public string $type;
    public array $elements;

    /**
     * @param non-empty-string $name
     * @param non-empty-string $title
     * @param CardFieldConfiguration[] $elements
     * @param non-empty-string $type
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, string $title, array $elements, string $type = 'section')
    {
        foreach ($elements as $element) {
            if (!$element instanceof CardFieldConfiguration) {
                throw new InvalidArgumentException(
                    sprintf(
                        'field element configuration must be «%s» type, %s given',
                        CardFieldConfiguration::class,
                        gettype($element)
                    )
                );
            }
        }

        $this->name = $name;
        $this->title = $title;
        $this->type = $type;
        $this->elements = $elements;
    }

    public function toArray(): array
    {
        $elements = [];
        foreach ($this->elements as $element) {
            $elements[]['name'] = $element->name;
        }

        return [
            'name' => $this->name,
            'title' => $this->title,
            'type' => $this->type,
            'elements' => $elements,
        ];
    }
}