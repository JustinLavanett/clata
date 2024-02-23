<?php

namespace TicketKitten\Service\Common\Auth;

use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Psr\Log\LoggerInterface;
use TicketKitten\DomainObjects\Status\UserStatus;
use TicketKitten\DomainObjects\UserDomainObject;
use TicketKitten\Exceptions\UnauthorizedException;
use TicketKitten\Service\Common\Auth\DTO\LoginResponse;

class LoginService
{
    private JWTAuth $jwtAuth;

    private LoggerInterface $logger;

    public function __construct(JWTAuth $jwtAuth, LoggerInterface $logger)
    {
        $this->jwtAuth = $jwtAuth;
        $this->logger = $logger;
    }

    /**
     * @throws UnauthorizedException
     */
    public function authenticate(string $email, string $password): LoginResponse
    {
        $token = $this->jwtAuth->attempt([
            'email' => strtolower($email),
            'password' => $password,
        ]);

        if (!$token) {
            throw new UnauthorizedException(__('Username or Password are incorrect'));
        }

        /** @var UserDomainObject $user */
        $user = UserDomainObject::hydrateFromModel($this->jwtAuth->user());

        if ($user->getStatus() !== UserStatus::ACTIVE->name) {
            $this->logger->info('Attempted login when user is not active', [
                'id' => $user->getId(),
            ]);

            throw new UnauthorizedException(__('User is not active'));
        }

        return new LoginResponse(
            token: $token,
            user: $user,
        );
    }
}
