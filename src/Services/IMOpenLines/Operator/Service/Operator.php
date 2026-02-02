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

namespace Bitrix24\SDK\Services\IMOpenLines\Operator\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IMOpenLines\Operator\Result\OperatorActionResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['imopenlines']))]
class Operator extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Takes the dialog for the current operator
     *
     * @param int $chatId Identifier of the chat that the current operator is responding to
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.operator.answer',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/operators/imopenlines-operator-answer.html',
        'Takes the dialog for the current operator'
    )]
    public function answer(int $chatId): OperatorActionResult
    {
        return new OperatorActionResult(
            $this->core->call('imopenlines.operator.answer', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Ends the dialogue by the current operator
     *
     * @param int $chatId The identifier of the chat that the current operator is ending
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.operator.finish',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/operators/imopenlines-operator-finish.html',
        'Ends the dialogue by the current operator'
    )]
    public function finish(int $chatId): OperatorActionResult
    {
        return new OperatorActionResult(
            $this->core->call('imopenlines.operator.finish', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Finishes the dialog of another operator
     *
     * @param int $chatId Identifier of the chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.operator.another.finish',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/operators/imopenlines-operator-another-finish.html',
        'Finishes the dialog of another operator'
    )]
    public function anotherFinish(int $chatId): OperatorActionResult
    {
        return new OperatorActionResult(
            $this->core->call('imopenlines.operator.another.finish', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Skips the dialog for the current operator
     *
     * @param int $chatId The identifier of the chat that the current operator skips
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.operator.skip',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/operators/imopenlines-operator-skip.html',
        'Skips the dialog for the current operator'
    )]
    public function skip(int $chatId): OperatorActionResult
    {
        return new OperatorActionResult(
            $this->core->call('imopenlines.operator.skip', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Marks the conversation as "spam" by the current operator
     *
     * @param int $chatId The identifier of the chat that the current operator marks as spam
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.operator.spam',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/operators/imopenlines-operator-spam.html',
        'Marks the conversation as "spam" by the current operator'
    )]
    public function spam(int $chatId): OperatorActionResult
    {
        return new OperatorActionResult(
            $this->core->call('imopenlines.operator.spam', [
                'CHAT_ID' => $chatId,
            ])
        );
    }

    /**
     * Transfers the dialogue to another operator or line
     *
     * @param int          $chatId     Identifier of the chat that the current operator is ending
     * @param int|string   $transferId Identifier of the entity to which the dialogue is being transferred.
     *                                 If the dialogue is to be transferred to an operator, the operator's ID is passed as the value.
     *                                 If to a line — the code in the format "queue#line ID#"
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'imopenlines.operator.transfer',
        'https://apidocs.bitrix24.com/api-reference/imopenlines/openlines/operators/imopenlines-operator-transfer.html',
        'Transfers the dialogue to another operator or line'
    )]
    public function transfer(int $chatId, int|string $transferId): OperatorActionResult
    {
        return new OperatorActionResult(
            $this->core->call('imopenlines.operator.transfer', [
                'CHAT_ID' => $chatId,
                'TRANSFER_ID' => $transferId,
            ])
        );
    }
}