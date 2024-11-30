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

use Bitrix24\SDK\Services\CRM\Activity\ActivityContentType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityDirectionType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityNotifyType;
use Bitrix24\SDK\Services\CRM\Activity\ActivityPriority;
use Bitrix24\SDK\Services\CRM\Activity\ActivityStatus;
use Bitrix24\SDK\Services\CRM\Activity\ActivityType;
use Carbon\CarbonImmutable;
use Typhoon\Reflection\TyphoonReflector;
use function Typhoon\Type\stringify;
use Money\Currency;

trait CustomBitrix24Assertions
{
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

        $this->assertEquals(
            $fieldCodesFromApi,
            $propsFromAnnotations,
            sprintf(
                'in phpdocs annotations for class %s we not found fields from actual api response: %s',
                $resultItemClassName,
                implode(', ', array_values(array_diff($fieldCodesFromApi, $propsFromAnnotations)))
            )
        );
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
                case 'crm_lead':
                case 'integer':
                case 'int':
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
                case 'crm_activity_binding':
                case 'crm_activity_communication':
                case 'crm_multifield':
                case 'uf_enum_element':
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
}