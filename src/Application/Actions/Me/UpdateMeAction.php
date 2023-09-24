<?php

declare(strict_types=1);

namespace App\Application\Actions\Me;

use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;

use App\Domain\User\User;
use Psr\Log\LoggerInterface;


class UpdateMeAction extends MeAction
{
    protected UserRepository $userRepository;
    protected LoggerInterface $logger;
    
    /**
     * {@inheritdoc}
     */
    public function action(): Response
    {
        $respdata = [];
        $data = (array) $this->request->getParsedBody();

        $header = $request->getHeaderLine($this->options["header"]);

        Assert::lazy()
        ->that($data)
        ->keyExists('uiid')
        ->verifyNow();
 
        Assert::lazy()
        ->that($data['uiid'])
        ->notEmpty()
        ->verifyNow();

        $user = $this->userRepository->findUserByUiid($data['uiid']);

        if(!$user){
            return $this->respondWithData("not found", 204);
        }

        if(key_exists('username',$data))$user->setUsername($data['username']);
        if(key_exists('password',$data))$user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        if(key_exists('firstname',$data))$user->setFirstname($data['firstname']);
        if(key_exists('lastname',$data))$user->setLastname($data['lastname']);
        if(key_exists('email',$data))$user->setEmail($data['email']);
        if(key_exists('uidkey',$data))$user->setUidkey($data['uidkey']);
        if(key_exists('level',$data))$user->setLevel(intval($data['level']));

        $this->userRepository->update($user);

        $respdata['result'] = 'updated';
        return $this->respondWithData($respdata, 201);
    }

}