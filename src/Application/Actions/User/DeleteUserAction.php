<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class DeleteUserAction extends UserAction
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

        $this->userRepository->delete($user);

        $respdata['result'] = 'deleted';

        return $this->respondWithData($respdata);
    }
}
