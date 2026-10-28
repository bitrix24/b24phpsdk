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

namespace Bitrix24\SDK\Services\CRM\CallList;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;
use Throwable;

/**
 * Adapts call list updates to their flat LIST_ID payload while retaining core batch transport.
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * @param array<int, array{ENTITY_TYPE: 'CONTACT'|'COMPANY', ENTITIES: list<int>, WEBFORM_ID?: int}> $entityItems
     * @return Generator<int, ResponseData>
     */
    #[\Override]
    public function updateEntityItems(string $apiMethod, array $entityItems): Generator
    {
        try {
            $this->clearCommands();
            foreach ($entityItems as $listId => $item) {
                if (!is_int($listId)) {
                    throw new InvalidArgumentException('Call list ID must be an integer');
                }

                $this->registerCommand($apiMethod, ['LIST_ID' => $listId] + $item);
            }

            yield from $this->getTraversable(true);
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new BaseException(sprintf('batch update call lists: %s', $exception->getMessage()), $exception->getCode(), $exception);
        }
    }
}
