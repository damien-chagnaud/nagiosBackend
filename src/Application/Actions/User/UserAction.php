<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use App\Domain\Token\TokenRepository;

use Psr\Log\LoggerInterface;

abstract class UserAction extends Action
{
    protected UserRepository $userRepository;
    protected TokenRepository $tokenRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository, TokenRepository $tokenRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

}
