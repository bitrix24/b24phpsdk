<?php

declare(strict_types=1);

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Activity\PhantomMethods;

use Bitrix24\SDK\Services\CRM\Activity\Service\ActivityType;
use Bitrix24\SDK\Tests\Integration\Fabric;

class ActivityTypePhantomMethods
{
    private readonly ActivityType $activityTypeService;

    /**
     * ActivityTypePhantomMethods constructor.
     */
    public function __construct()
    {
        $this->activityTypeService = Fabric::getServiceBuilder(true)->getCRMScope()->activityType();
    }

    /**
     * Get Activity type fields from list response
     */
    public function getFields(): array
    {
        $list = $this->activityTypeService->list()->getActivityTypes();
        $fields = [];

        if ($list !== [] && is_array($list)) {

            $res = $list[0]->getData();

            $i = 0;
            foreach (array_keys($res) as $key) {
                $fields[$i] = $key;
                $i++;
            }

            unset($i);
        }

        return $fields;
    }

    /**
     * Get Activity type fields description from list response
     */
    public function getFieldsDescription(): array
    {
        $list = $this->activityTypeService->list()->getActivityTypes();
        $fields = [];

        if ($list !== [] && is_array($list)) {

            $res = $list[0]->getData();

            $i = 0;
            foreach ($res as $key => $value) {
                $type = '';

                if (is_string($value)) {
                    $type = 'string';
                } elseif (is_int($value)) {
                    $type = 'int';
                } elseif (is_bool($value)) {
                    $type = 'bool';
                } elseif (is_array($value)) {
                    $type = 'array';
                }

                $fields[$key] = ['type' => $type];

                $i++;
            }

            unset($i);
        }

        return $fields;
    }
}
