<?php

namespace TicketKitten\Service\Handler\Auth;

use Illuminate\Database\DatabaseManager;
use Illuminate\Hashing\HashManager;
use Illuminate\Mail\Mailer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;
use TicketKitten\DomainObjects\UserDomainObject;
use TicketKitten\Exceptions\PasswordInvalidException;
use TicketKitten\Http\DataTransferObjects\ResetPasswordDTO;
use TicketKitten\Mail\ResetPasswordSuccess;
use TicketKitten\Repository\Interfaces\PasswordResetTokenRepositoryInterface;
use TicketKitten\Repository\Interfaces\UserRepositoryInterface;
use TicketKitten\Service\Common\Auth\ResetPasswordTokenValidateService;

class ResetPasswordHandler
{
    private UserRepositoryInterface $userRepository;
    private Mailer $mailer;
    private PasswordResetTokenRepositoryInterface $passwordResetTokenRepository;
    private HashManager $hashManager;
    private DatabaseManager $databaseManager;
    private LoggerInterface $logger;
    private ResetPasswordTokenValidateService $passwordTokenValidateService;

    public function __construct(
        UserRepositoryInterface               $userRepository,
        PasswordResetTokenRepositoryInterface $passwordResetTokenRepository,
        Mailer                                $mailer,
        HashManager                           $hashManager,
        DatabaseManager                       $databaseManager,
        LoggerInterface                       $logger,
        ResetPasswordTokenValidateService     $passwordTokenValidateService,
    )
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->passwordResetTokenRepository = $passwordResetTokenRepository;
        $this->hashManager = $hashManager;
        $this->databaseManager = $databaseManager;
        $this->logger = $logger;
        $this->passwordTokenValidateService = $passwordTokenValidateService;
    }

    /**
     * @throws Throwable
     */
    public function handle(ResetPasswordDTO $resetPasswordData): void
    {
        $this->databaseManager->transaction(function () use ($resetPasswordData) {
            $resetToken = $this->passwordTokenValidateService->validateAndFetchToken($resetPasswordData->token);
            $user = $this->validateUser($resetToken->getEmail());

            $this->validateCurrentPassword($user, $resetPasswordData->currentPassword);
            $this->resetUserPassword($user->getId(), $resetPasswordData->password);
            $this->deleteResetToken($resetToken->getEmail());
            $this->logResetPasswordSuccess($user);
            $this->sendResetPasswordEmail($user);
        });
    }

    private function validateUser(string $email): UserDomainObject
    {
        $user = $this->userRepository->findFirstWhere(['email' => $email]);
        if (!$user) {
            throw new ResourceNotFoundException(__('User not found'));
        }

        return $user;
    }

    private function resetUserPassword(int $userId, string $newPassword): void
    {
        $this->userRepository->updateWhere(
            attributes: [
                'password' => $this->hashManager->make($newPassword)
            ],
            where: [
                'id' => $userId
            ],
        );
    }

    private function deleteResetToken(string $email): void
    {
        $this->passwordResetTokenRepository->deleteWhere(['email' => $email]);
    }

    private function logResetPasswordSuccess($user): void
    {
        $this->logger->info('Password reset successfully', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail()
            ]
        );
    }

    private function sendResetPasswordEmail($user): void
    {
        $this->mailer->to($user->getEmail())->send(new ResetPasswordSuccess());
    }

    /**
     * @throws PasswordInvalidException
     */
    private function validateCurrentPassword(UserDomainObject $user, string $currentPassword): void
    {
        if (!$this->hashManager->check($currentPassword, $user->getPassword())) {
            throw new PasswordInvalidException(__('Current password is incorrect'));
        }
    }
}
