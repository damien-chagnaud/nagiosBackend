<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Domain\User\UserRepository;
use App\Domain\Token\TokenRepository;
use Psr\Http\Message\ResponseInterface as Response;
use function uuid_create;
use App\Application\Actions\Action;
use Psr\Log\LoggerInterface;


class GenerateTokenAction extends UserAction
{
    protected UserRepository $userRepository;
    protected TokenRepository $tokenRepository;
    protected LoggerInterface $logger;
    
    /**
     * {@inheritdoc}
     */
    public function action(): Response
    {

        //string $token;
        $this->logger->info("ICI");

        $token = "";

        try{
            $userName = (string) $this->request->getAttribute('user');

            $this->logger->info("user:".$userName);

            $id = uuid_create(UUID_TYPE_RANDOM);

            //Retrive User data
            $user = $this->userRepository->findUserByName($userName);

            if(null === $user){
                return $this->respondWithData("Error user not found");
            }

            $uiid = $user->getId();

            $this->logger->info("userID:".$uiid);

            $token = $this->tokenRepository->generateToken($uiid, $id);

        } catch (Exception $exception) {
            $this->logger->log(LogLevel::WARNING, $exception->getMessage(), [$token]);
            return $this->respondWithData("Sever Error");
        }

        $response["token"] = $token ;

        return $this->respondWithData($response);

        
    }
}
