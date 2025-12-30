<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Block\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Landing\Block\Result\BlocksResult;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockResult;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockContentResult;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockManifestResult;
use Bitrix24\SDK\Services\Landing\Block\Result\RepositoryResult;
use Bitrix24\SDK\Services\Landing\Block\Result\RepositoryContentResult;
use Bitrix24\SDK\Services\Landing\Block\Result\UploadFileResult;
use Bitrix24\SDK\Services\Landing\Block\Result\UpdateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class Block extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Get list of page blocks.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-list.html
     *
     * @param int|array $lid Page identifier or array of identifiers
     * @param array $params Parameters: edit_mode (0|1), deleted (0|1)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.getlist',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-list.html',
        'Method retrieves a list of page blocks.'
    )]
    public function list($lid, array $params = []): BlocksResult
    {
        return new BlocksResult(
            $this->core->call(
                'landing.block.getlist',
                [
                    'lid' => $lid,
                    'params' => $params,
                ]
            )
        );
    }

    /**
     * Get block by ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-by-id.html
     *
     * @param int $blockId Block identifier
     * @param array $params Parameters: edit_mode (0|1)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.getbyid',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-by-id.html',
        'Method retrieves a block by its identifier.'
    )]
    public function getById(int $blockId, array $params = []): BlockResult
    {
        return new BlockResult(
            $this->core->call(
                'landing.block.getbyid',
                [
                    'block' => $blockId,
                    'params' => $params,
                ]
            )
        );
    }

    /**
     * Get content of block.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-content.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param int $editMode Editing mode (1) or not (0)
     * @param array $params Additional parameters: wrapper_show (0|1)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.getcontent',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-content.html',
        'Method retrieves the content of a block.'
    )]
    public function getContent(int $lid, int $blockId, int $editMode = 0, array $params = []): BlockContentResult
    {
        return new BlockContentResult(
            $this->core->call(
                'landing.block.getcontent',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'editMode' => $editMode,
                    'params' => $params,
                ]
            )
        );
    }

    /**
     * Get block manifest.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-manifest.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param array $params Parameters: edit_mode (0|1)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.getmanifest',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-manifest.html',
        'Method retrieves the manifest of a specific block already placed on the page.'
    )]
    public function getManifest(int $lid, int $blockId, array $params = []): BlockManifestResult
    {
        return new BlockManifestResult(
            $this->core->call(
                'landing.block.getmanifest',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'params' => $params,
                ]
            )
        );
    }

    /**
     * Get list of blocks from repository.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-repository.html
     *
     * @param string $section Section code of the repository
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.getrepository',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-repository.html',
        'Method returns a list of blocks from the repository.'
    )]
    public function getRepository(string $section): RepositoryResult
    {
        return new RepositoryResult(
            $this->core->call(
                'landing.block.getrepository',
                [
                    'section' => $section,
                ]
            )
        );
    }

    /**
     * Get block manifest from repository.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-manifest-file.html
     *
     * @param string $blockCode Block code
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.getmanifestfile',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-manifest-file.html',
        'Method retrieves the manifest of a block from the repository.'
    )]
    public function getManifestFile(string $blockCode): BlockManifestResult
    {
        return new BlockManifestResult(
            $this->core->call(
                'landing.block.getmanifestfile',
                [
                    'code' => $blockCode,
                ]
            )
        );
    }

    /**
     * Get block content from repository.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-content-from-repository.html
     *
     * @param string $blockCode Block code
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.getcontentfromrepository',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-content-from-repository.html',
        'Method retrieves the content of a block from the repository "as is" before adding the block to any page.'
    )]
    public function getContentFromRepository(string $blockCode): RepositoryContentResult
    {
        return new RepositoryContentResult(
            $this->core->call(
                'landing.block.getcontentfromrepository',
                [
                    'code' => $blockCode,
                ]
            )
        );
    }

    /**
     * Update block content.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-nodes.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param array $data Array of selectors and new values
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.updatenodes',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-nodes.html',
        'Method changes the content of the block.'
    )]
    public function updateNodes(int $lid, int $blockId, array $data): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.updatenodes',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'data' => $data,
                ]
            )
        );
    }

    /**
     * Update block node attributes.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-attrs.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param array $data Data for changing node attributes
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.updateattrs',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-attrs.html',
        'Method for changing the attributes of a block node.'
    )]
    public function updateAttrs(int $lid, int $blockId, array $data): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.updateattrs',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'data' => $data,
                ]
            )
        );
    }

    /**
     * Update block styles.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-styles.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param array $data Array of selectors with classList and affect parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.updatestyles',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-styles.html',
        'Method changes the styles of the block.'
    )]
    public function updateStyles(int $lid, int $blockId, array $data): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.updatestyles',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'data' => $data,
                ]
            )
        );
    }

    /**
     * Update block content with arbitrary content.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-content.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param string $content New content for the block
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.updatecontent',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-content.html',
        'Method updates the content of a block already placed on the page to any arbitrary content.'
    )]
    public function updateContent(int $lid, int $blockId, string $content): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.updatecontent',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'content' => $content,
                ]
            )
        );
    }

    /**
     * Bulk update block cards.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-cards.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param array $data Data for bulk updating cards
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.updatecards',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-update-cards.html',
        'Method for bulk updating block cards.'
    )]
    public function updateCards(int $lid, int $blockId, array $data): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.updatecards',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'data' => $data,
                ]
            )
        );
    }

    /**
     * Clone block card.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-clone-card.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param string $selector Card selector (e.g., '.landing-block-card@0')
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.clonecard',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-clone-card.html',
        'Method clones a block card.'
    )]
    public function cloneCard(int $lid, int $blockId, string $selector): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.clonecard',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'selector' => $selector,
                ]
            )
        );
    }

    /**
     * Add card with modified content.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-add-card.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param string $selector Card selector (e.g., '.landing-block-card@0')
     * @param string $content Content of the new card
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.addcard',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-add-card.html',
        'Method fully replicates the work of landing.block.clonecard but allows inserting a card with modified content right away.'
    )]
    public function addCard(int $lid, int $blockId, string $selector, string $content): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.addcard',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'selector' => $selector,
                    'content' => $content,
                ]
            )
        );
    }

    /**
     * Remove block card.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-remove-card.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param string $selector Card selector (e.g., '.landing-block-card@0')
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.removecard',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-remove-card.html',
        'Method removes a block card.'
    )]
    public function removeCard(int $lid, int $blockId, string $selector): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.removecard',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'selector' => $selector,
                ]
            )
        );
    }

    /**
     * Upload and attach image to block.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-upload-file.html
     *
     * @param int $blockId Block identifier
     * @param mixed $picture Image data (URL string, file element, or array with name and base64 content)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.uploadfile',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-upload-file.html',
        'Method uploads an image and associates it with the specified block.'
    )]
    public function uploadFile(int $blockId, mixed $picture): UploadFileResult
    {
        return new UploadFileResult(
            $this->core->call(
                'landing.block.uploadfile',
                [
                    'block' => $blockId,
                    'picture' => $picture,
                ]
            )
        );
    }

    /**
     * Change anchor symbol code.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-change-anchor.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param string $anchor New anchor code
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.changeanchor',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-change-anchor.html',
        'Method changes the symbolic code of the anchor.'
    )]
    public function changeAnchor(int $lid, int $blockId, string $anchor): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.changeanchor',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'data' => $anchor,
                ]
            )
        );
    }

    /**
     * Change tag name.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-change-node-name.html
     *
     * @param int $lid Page identifier
     * @param int $blockId Block identifier
     * @param array $data Array of selectors and new tag names. Example: ['.landing-block-node-text@0' => 'h2']
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.block.changenodename',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-change-node-name.html',
        'Method for changing the tag name.'
    )]
    public function changeNodeName(int $lid, int $blockId, array $data): UpdateResult
    {
        return new UpdateResult(
            $this->core->call(
                'landing.block.changenodename',
                [
                    'lid' => $lid,
                    'block' => $blockId,
                    'data' => $data,
                ]
            )
        );
    }
}
