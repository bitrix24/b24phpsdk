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

namespace Bitrix24\SDK\Application\Workflows\Robots\Service;

use Bitrix24\SDK\Application\Workflows\Robots\Events\RobotAdded;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RobotManager
{
    protected EventDispatcherInterface $eventDispatcher;
    protected LoggerInterface $logger;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * @param ServiceBuilder $serviceBuilder
     * @param array $robotsMetadata
     * @param string $defaultHandlerUrl
     * @param int|null $defaultB24UserId
     * @return void
     * @throws BaseException
     * @throws TransportException
     */
    public function install(
        ServiceBuilder $serviceBuilder,
        array $robotsMetadata,
        ?string $defaultHandlerUrl,
        ?int $defaultB24UserId
    ): void {
        // todo check duplicates in robots metadata


        // get installed robots
        $installedRobots = $serviceBuilder->getBizProcScope()->robot()->list();


        // skip already installed robots
        // todo uninstall robots for update if metadata are not equal

        // install robots
        // todo add in batch mode
        foreach ($robotsMetadata as $robotMetadata) {
            $isAdded = $serviceBuilder->getBizProcScope()->robot()->add(
                $robotMetadata->code,
                $robotMetadata->handlerUrl,
                $robotMetadata->b24AuthUserId,
                $robotMetadata->localizedRobotName,
                $robotMetadata->isUseSubscription,
                $robotMetadata->properties,
                $robotMetadata->isUsePlacement,
                $robotMetadata->returnProperties
            )->isSuccess();

            $this->logger->debug('RobotManager.install.robotAdded', [
                'code' => $robotMetadata->code,
            ]);

            if ($isAdded) {
                $this->eventDispatcher->dispatch(new RobotAdded($robotMetadata));
            }
        }
    }
}