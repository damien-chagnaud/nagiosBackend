<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class ListUserAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $respdata = [];
        $users = $this->userRepository->findAll();
        
        foreach($users as $user){
            $respdata[$user->getId()] = [
                'username'=>$user->getUsername(),
                'firstname'=>$user->getFirstname(), 
                'lastname'=>$user->getLastname(), 
                'email'=>$user->getEmail(), 
                'level'=>$user->getLevel()
            ];
        }

        return $this->respondWithData($respdata);
    }
}
