<?php

namespace TicketKitten\Service\Handler\User;

use TicketKitten\DomainObjects\Status\UserStatus;
use TicketKitten\DomainObjects\UserDomainObject;
use TicketKitten\Exceptions\ResourceConflictException;
use TicketKitten\Http\DataTransferObjects\CreateUserDTO;
use TicketKitten\Repository\Interfaces\AccountRepositoryInterface;
use TicketKitten\Repository\Interfaces\UserRepositoryInterface;
use TicketKitten\Service\Common\User\SendUserInvitationService;

class CreateUserHandler
{
    private UserRepositoryInterface $userRepository;

    private AccountRepositoryInterface $accountRepository;

    private SendUserInvitationService $sendUserInvitationService;

    public function __construct(
        UserRepositoryInterface    $userRepository,
        AccountRepositoryInterface $accountRepository,
        SendUserInvitationService  $sendUserInvitationService
    )
    {
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
        $this->sendUserInvitationService = $sendUserInvitationService;
    }

    /**
     * @throws ResourceConflictException
     */
    public function handle(CreateUserDTO $userData): UserDomainObject
    {
        $this->checkForExistingUser($userData);

        $invitedUser = $this->createUser($userData);

        $this->sendUserInvitationService->sendInvitation($invitedUser);

        return $invitedUser;
    }

    private function createUser(CreateUserDTO $userData): UserDomainObject
    {
        $authenticatedAccount = $this->accountRepository->findById($userData->account_id);

        return $this->userRepository->create([
            'first_name' => $userData->first_name,
            'last_name' => $userData->last_name,
            'email' => strtolower($userData->email),
            'password' => 'invited', // initially, a user is in an invited state, so they don't have a password
            'invited_by' => $userData->invited_by,
            'account_id' => $userData->account_id,
            'status' => UserStatus::INVITED->name,
            'timezone' => $authenticatedAccount->getTimezone(),
            'role' => $userData->role->name,
        ]);
    }

    /**
     * @throws ResourceConflictException
     */
    private function checkForExistingUser(CreateUserDTO $userData): void
    {
        $existingUser = $this->userRepository->findFirstWhere([
            'email' => $userData->email,
        ]);

        if ($existingUser !== null) {
            throw new ResourceConflictException(
                __('The email :email already exists', [
                    'email' => $userData->email,
                ])
            );
        }
    }
}
