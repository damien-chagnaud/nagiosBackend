<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;

use App\Domain\User\User;
use function uuid_create;
use Psr\Log\LoggerInterface;


class NewUserAction extends UserAction
{
    const LEVEL = 2;
    protected UserRepository $userRepository;
    protected LoggerInterface $logger;
    
    /**
     * {@inheritdoc}
     */
    public function action(): Response
    {
        $data = (array) $this->request->getParsedBody();
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        Assert::lazy()
        ->that($data)
        ->keyExists('username')
        ->that($data)
        ->keyExists('password')
        ->that($data)
        ->keyExists('firstname')
        ->that($data)
        ->keyExists('lastname')
        ->that($data)
        ->keyExists('email')
        ->that($data)
        ->keyExists('uidkey')
        ->that($data)
        ->keyExists('level')
        ->verifyNow();
 
        Assert::lazy()
        ->that($data['username'])
        ->notEmpty()
        ->that($data['password'])
        ->notEmpty()
        ->that($data['firstname'])
        ->notEmpty()
        ->that($data['lastname'])
        ->notEmpty()
        ->that($data['email'])
        ->notEmpty()
        ->that($data['level'])
        ->notEmpty()
        ->verifyNow();
 
        $this->logger->info('Create tampon');
        $userObj = new User($user["id"],);
           
        $user = new User(
            $uuid,
            $data["username"], 
            $data["password"], 
            $data["firstname"], 
            $data["lastname"], 
            $data["email"], 
            $data["uidkey"], 
        intval($data["level"]),
        );
 
        $this->logger->info('Add tampon');
        $this->userRepository->add($user);
 
        $this->logger->info('User was created. with:'.$uuid);

        $respdata['usernamer'] = $data["username"];
 
        return $this->respondWithData( $respdata, 201);

    

    }

}