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

namespace Bitrix24\SDK\Tests\CustomAssertions;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Services\CRM\Activity\ActivityContentType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityDirectionType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityNotifyType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityPriority;
use Bitrix24\SDK\Services\CRM\Activity\ActivityStatus;
use Bitrix24\SDK\Services\CRM\Activity\ActivityType;
use Carbon\CarbonImmutable;
use MoneyPHP\Percentage\Percentage;
use Typhoon\Reflection\TyphoonReflector;
use function Typhoon\Type\stringify;
use Money\Currency;

trait CustomBitrix24Assertions
{
    /**
     * Assert that every property-read field of $resultItemClassName, when accessed via magic getter on $item,
     * returns a value whose PHP type matches the PHPDoc annotation.
     *
     * @param class-string $resultItemClassName
     */
    protected function assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
        AbstractItem $item,
        string $resultItemClassName
    ): void {
        $props = TyphoonReflector::build()->reflectClass($resultItemClassName)->properties();

        foreach ($props as $meta) {
            if (!$meta->isAnnotated() || $meta->isNative()) {
                continue;
            }

            $propName = $meta->id->name;
            $typeStr = stringify($meta->type());
            $value = $item->$propName;

            // null is always valid for nullable types
            if (str_contains($typeStr, 'null') && $value === null) {
                continue;
            }

            $message = sprintf(
                'field «%s» in «%s» annotated as «%s» but actual PHP type is «%s»',
                $propName,
                $resultItemClassName,
                $typeStr,
                get_debug_type($value)
            );

            // For nullable union types like "Carbon\CarbonImmutable|null", strip null before assertInstanceOf
            $classStr = implode('|', array_values(array_filter(
                explode('|', $typeStr),
                static fn (string $t): bool => $t !== 'null'
            )));

            match (true) {
                str_contains($typeStr, 'array')  => $this->assertIsArray($value, $message),
                str_contains($typeStr, 'bool')   => $this->assertIsBool($value, $message),
                str_contains($typeStr, 'int')    => $this->assertIsInt($value, $message),
                str_contains($typeStr, 'float')  => $this->assertIsFloat($value, $message),
                str_contains($typeStr, 'string') => $this->assertIsString($value, $message),
                default                          => $this->assertInstanceOf($classStr, $value, $message),
            };
        }
    }

    /**
     * @param array<int, non-empty-string> $fieldCodesFromApi
     * @param class-string $resultItemClassName
     * @return void
     */
    protected function assertBitrix24AllResultItemFieldsAnnotated(
        array $fieldCodesFromApi,
        string $resultItemClassName
    ): void {
        sort($fieldCodesFromApi);

        // parse keys from phpdoc annotation
        $props = TyphoonReflector::build()->reflectClass($resultItemClassName)->properties();
        $propsFromAnnotations = [];
        foreach ($props as $meta) {
            if ($meta->isAnnotated() && !$meta->isNative()) {
                $propsFromAnnotations[] = $meta->id->name;
            }
        }
        sort($propsFromAnnotations);

        if (count($fieldCodesFromApi) >= $propsFromAnnotations) {
            $this->assertEquals(
                $fieldCodesFromApi,
                $propsFromAnnotations,
                sprintf(
                    'in phpdocs annotations for class «%s» we not found fields from actual api response: «%s»',
                    $resultItemClassName,
                    implode(', ', array_values(array_diff($fieldCodesFromApi, $propsFromAnnotations)))
                )
            );
        } else {
            $this->assertEquals(
                $fieldCodesFromApi,
                $propsFromAnnotations,
                sprintf(
                    'in api response for class «%s» we not found some fields from class annotation: «%s»',
                    $resultItemClassName,
                    implode(', ', array_values(array_diff($propsFromAnnotations, $fieldCodesFromApi)))
                )
            );
        }
    }

    protected function assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
        array $fieldCodesFromApi,
        string $resultItemClassName
    ): void {
        // parse keys from phpdoc annotation
        $props = TyphoonReflector::build()->reflectClass($resultItemClassName)->properties();
        $propsFromAnnotations = [];
        foreach ($props as $meta) {
            if ($meta->isAnnotated() && !$meta->isNative()) {
                $propsFromAnnotations[$meta->id->name] = stringify($meta->type());
            }
        }

        asort($propsFromAnnotations);
        asort($fieldCodesFromApi);
        foreach ($fieldCodesFromApi as $fieldCode => $fieldData) {
            // mapping internal bitrix24 types to bitrix24 sdk types
            switch ($fieldData['type']) {
                case 'string':
                case 'crm_currency':
                case 'crm_status':
                    if (str_contains($fieldCode, 'ACTIVE')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'bool'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «bool»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type']
                            )
                        );
                        break;
                    }
                    // if field code contains currency
                    if (str_contains($fieldCode, 'CURRENCY_ID')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], Currency::class),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                Currency::class
                            )
                        );
                        break;
                    }
                    if (str_contains($fieldCode, 'EDIT_FORM_LABEL') ||
                        str_contains($fieldCode, 'LIST_COLUMN_LABEL') ||
                        str_contains($fieldCode, 'LIST_FILTER_LABEL')

                    ) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'array'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'array'
                            )
                        );
                        break;
                    }

                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'string'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'string'
                        )
                    );
                    break;
                case 'user':
                case 'crm_enum_ownertype':
                case 'integer':
                case 'int':
                case 'mail_message':
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'int'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'int'
                        )
                    );
                    break;
                case 'double':
                    if (str_contains(mb_strtoupper($fieldCode), 'SORTING')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'string'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'string'
                            )
                        );
                        break;
                    }
                    if (str_contains(mb_strtoupper($fieldCode), 'QUANTITY')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'string'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'string'
                            )
                        );
                        break;
                    }
                    if (str_contains(mb_strtoupper($fieldCode), 'WEIGHT')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'string'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'string'
                            )
                        );
                        break;
                    }
                    if (str_contains(mb_strtoupper($fieldCode), 'RATE')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], Percentage::class),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                Percentage::class
                            )
                        );
                        break;
                    }
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'Money\Money'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'Money\Money|null'
                        )
                    );
                    break;
                case 'date':
                case 'datetime':
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], CarbonImmutable::class),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            CarbonImmutable::class
                        )
                    );
                    break;
                case 'char':
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'bool'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'bool'
                        )
                    );
                    break;
                case 'enum':
                    if (str_contains($fieldCode, 'DELETED_TYPE')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'int'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'int|null'
                            )
                        );

                        break;
                    }
                    if (str_contains($fieldCode, 'durationType')
                        || str_contains($fieldCode, 'mark')
                        || str_contains($fieldCode, 'TYPE')
                    ) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'string'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'string|null'
                            )
                        );

                        break;
                    }
                    if (str_contains($fieldCode, 'priority')
                        || str_contains($fieldCode, 'status')
                    ) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'int'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'int|null'
                            )
                        );

                        break;
                    }
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'bool'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'bool'
                        )
                    );
                    break;
                case 'file':
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'File'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'File|null'
                        )
                    );
                    break;
                case 'diskfile':
                case 'object':
                case 'crm_company':
                case 'crm_contact':
                case 'crm_deal':
                case 'crm_lead':
                case 'location':
                case 'product_file':
                    if (str_contains($fieldCode, '_IDS') ||
                        str_contains($fieldCode, 'PHOTO') ||
                        str_contains($fieldCode, 'SETTINGS') ||
                        str_contains($fieldCode, '_PICTURE')) {
                        $this->assertTrue(
                            str_contains($propsFromAnnotations[$fieldCode], 'array'),
                            sprintf(
                                'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                                $resultItemClassName,
                                $fieldCode,
                                $propsFromAnnotations[$fieldCode],
                                $fieldData['type'],
                                'array'
                            )
                        );
                        break;
                    }
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'int'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'int'
                        )
                    );
                    break;
                case 'crm_enum_activitydirection':
                    $this->assertEquals(
                        ActivityDirectionType::class,
                        $propsFromAnnotations[$fieldCode],
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            ActivityDirectionType::class
                        )
                    );
                    break;
                case 'crm_enum_contenttype':
                    $this->assertEquals(
                        ActivityContentType::class,
                        $propsFromAnnotations[$fieldCode],
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            ActivityContentType::class
                        )
                    );
                    break;
                case 'crm_enum_activitytype':
                    $this->assertEquals(
                        ActivityType::class,
                        $propsFromAnnotations[$fieldCode],
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            ActivityType::class
                        )
                    );
                    break;
                case 'crm_enum_activitynotifytype':
                    $this->assertEquals(
                        ActivityNotifyType::class,
                        $propsFromAnnotations[$fieldCode],
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            ActivityNotifyType::class
                        )
                    );
                    break;
                case 'crm_enum_activitypriority':
                    $this->assertEquals(
                        ActivityPriority::class,
                        $propsFromAnnotations[$fieldCode],
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            ActivityPriority::class
                        )
                    );
                    break;
                case 'crm_enum_activitystatus':
                    $this->assertEquals(
                        ActivityStatus::class,
                        $propsFromAnnotations[$fieldCode],
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            ActivityStatus::class
                        )
                    );
                    break;
                case 'array':
                case 'crm':
                case 'crm_activity_binding':
                case 'crm_activity_communication':
                case 'crm_multifield':
                case 'uf_enum_element':
                case 'currency_localization':
                case 'crm_status_extra':
                case 'attached_diskfile':
                case 'disk_file':
                case 'datatype':
                    $this->assertTrue(
                        str_contains($propsFromAnnotations[$fieldCode], 'array'),
                        sprintf(
                            'class «%s» field «%s» has invalid type phpdoc annotation «%s», field type from bitrix24 is «%s», expected sdk-type «%s»',
                            $resultItemClassName,
                            $fieldCode,
                            $propsFromAnnotations[$fieldCode],
                            $fieldData['type'],
                            'array'
                        )
                    );
                    break;
                default:
                    $this->assertFalse(
                        true,
                        sprintf(
                            'class «%s» field «%s» has unknown field type from bitrix24 «%s», sdk-type from annotation «%s», fix type mapping map in integration test',
                            $resultItemClassName,
                            $fieldCode,
                            $fieldData['type'],
                            $propsFromAnnotations[$fieldCode],
                        )
                    );
            }
        }
    }

    /**
     * Assert that for each annotated property of a result item the actual PHP value
     * returned via the magic getter is compatible with the phpdoc type annotation.
     *
     * Note: Bitrix24 REST API may return integer fields as strings,
     * so a string value is considered compatible with an «int» annotation.
     * Raw datetime strings are considered compatible with «CarbonImmutable» annotations
     * because AbstractItem does not perform any type casting.
     *
     * @param class-string $resultItemClassName
     */
    protected function assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
        AbstractItem $resultItem,
        string $resultItemClassName
    ): void {
        $props = TyphoonReflector::build()->reflectClass($resultItemClassName)->properties();

        foreach ($props as $meta) {
            if (!$meta->isAnnotated() || $meta->isNative()) {
                continue;
            }

            $propName = $meta->id->name;
            $annotatedType = stringify($meta->type());

            /** @var mixed $actualValue */
            $actualValue = $resultItem->$propName;

            if ($actualValue === null) {
                $this->assertStringContainsString(
                    'null',
                    $annotatedType,
                    sprintf(
                        'class «%s» field «%s» returned null but annotation «%s» does not allow null',
                        $resultItemClassName,
                        $propName,
                        $annotatedType
                    )
                );
                continue;
            }

            $actualPhpType = get_debug_type($actualValue);

            if (is_array($actualValue)) {
                $this->assertStringContainsString(
                    'array',
                    $annotatedType,
                    sprintf(
                        'class «%s» field «%s» annotation «%s» does not match actual PHP type «array»',
                        $resultItemClassName,
                        $propName,
                        $annotatedType
                    )
                );
                continue;
            }

            if (is_bool($actualValue)) {
                $this->assertTrue(
                    str_contains($annotatedType, 'bool'),
                    sprintf(
                        'class «%s» field «%s» annotation «%s» does not match actual PHP type «bool»',
                        $resultItemClassName,
                        $propName,
                        $annotatedType
                    )
                );
                continue;
            }

            if ($actualValue instanceof CarbonImmutable) {
                $this->assertStringContainsString(
                    'CarbonImmutable',
                    $annotatedType,
                    sprintf(
                        'class «%s» field «%s» annotation «%s» does not match actual PHP type «CarbonImmutable»',
                        $resultItemClassName,
                        $propName,
                        $annotatedType
                    )
                );
                continue;
            }

            if (is_int($actualValue)) {
                $this->assertTrue(
                    str_contains($annotatedType, 'int'),
                    sprintf(
                        'class «%s» field «%s» annotation «%s» does not match actual PHP type «int»',
                        $resultItemClassName,
                        $propName,
                        $annotatedType
                    )
                );
                continue;
            }

            if (is_string($actualValue)) {
                // Bitrix24 REST API often returns integer IDs as strings;
                // raw datetime values are also returned as strings before casting.
                $this->assertTrue(
                    str_contains($annotatedType, 'string')
                    || str_contains($annotatedType, 'int')
                    || str_contains($annotatedType, 'CarbonImmutable'),
                    sprintf(
                        'class «%s» field «%s» annotation «%s» does not cover actual PHP type «string»',
                        $resultItemClassName,
                        $propName,
                        $annotatedType
                    )
                );
                continue;
            }

            // Fallback for other types: the debug type name must appear in the annotation string.
            $this->assertStringContainsString(
                $actualPhpType,
                $annotatedType,
                sprintf(
                    'class «%s» field «%s» annotation «%s» does not match actual PHP type «%s»',
                    $resultItemClassName,
                    $propName,
                    $annotatedType,
                    $actualPhpType
                )
            );
        }
    }
}
