<?php

namespace TicketKitten\Http\Actions\Auth;

use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TicketKitten\Exceptions\DecryptionFailedException;
use TicketKitten\Exceptions\EncryptedPayloadExpiredException;
use TicketKitten\Http\Actions\BaseAction;
use TicketKitten\Http\ResponseCodes;
use TicketKitten\Repository\Interfaces\UserRepositoryInterface;
use TicketKitten\Resources\User\UserResource;
use TicketKitten\Service\Common\EncryptedPayloadService;

class GetUserInvitationAction extends BaseAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EncryptedPayloadService $encryptedPayloadService,
        private readonly LoggerInterface         $logger,
    )
    {
    }

    public function __invoke(string $inviteToken): JsonResponse
    {
        try {
            ['user_id' => $userId, 'email' => $email] = $this->encryptedPayloadService->decryptPayload($inviteToken);
        } catch (EncryptedPayloadExpiredException) {
            throw new HttpException(ResponseCodes::HTTP_GONE, __('The invitation has expired'));
        } catch (DecryptionFailedException) {
            throw new HttpException(ResponseCodes::HTTP_BAD_REQUEST, __('The invitation is invalid'));
        }

        $user = $this->userRepository->findFirstWhere([
            'id' => $userId,
            'email' => $email,
        ]);

        if (!$user) {
            $this->logger->info(__('Invitation valid, but user not found'), [
                'user_id' => $userId,
                'email' => $email,
            ]);

            throw new NotFoundHttpException();
        }

        return $this->resourceResponse(UserResource::class, $user);
    }
}
