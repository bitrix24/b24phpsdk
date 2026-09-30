<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Bitrix24\SDK\Services\Log\BlogPost\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $ID
 * @property-read int $BLOG_ID
 * @property-read int $AUTHOR_ID
 * @property-read int $NUM_COMMENTS
 * @property-read string $PUBLISH_STATUS
 * @property-read string $TITLE
 * @property-read string $ENABLE_COMMENTS
 * @property-read string $MICRO
 * @property-read string $DETAIL_TEXT
 * @property-read string $HAS_SOCNET_ALL
 * @property-read string $HAS_TAGS
 * @property-read string $HAS_IMAGES
 * @property-read string|null $HAS_PROPS
 * @property-read string|null $CODE
 * @property-read string|null $CATEGORY_ID
 * @property-read string|null $HAS_COMMENT_IMAGES
 * @property-read CarbonImmutable|null $DATE_PUBLISH
 * @property-read array|null $FILES
 * @property-read array|null $UF_BLOG_POST_DOC
 * @property-read array|null $UF_BLOG_POST_URL_PRV
 * @property-read array|null $UF_GRATITUDE
 * @property-read array|null $UF_BLOG_POST_FILE
 * @property-read array|null $UF_BLOG_POST_IMPRTNT
 * @property-read array|null $UF_IMPRTANT_DATE_END
 * @property-read array|null $UF_BLOG_POST_VOTE
 * @property-read array|null $UF_MAIL_MESSAGE
 */
class BlogPostItemResult extends AbstractAnnotatedItem
{
}
