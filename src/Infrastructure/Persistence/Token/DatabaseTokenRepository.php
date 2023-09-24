<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Token;

use App\Domain\Token\Token;
use App\Domain\Token\TokenNotFoundException;
use App\Domain\Token\TokenRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use function uuid_create;

class DatabaseTokenRepository implements TokenRepository
{

    private EntityRepository $repository;
    private EntityManager $entity_manager;

    public function __construct(EntityManager $entity_manager)
    {
        $this->repository = $entity_manager->getRepository(Token::class);
        $this->entity_manager = $entity_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function findTokenOfUiid(string $uiid): Token
    {
        /** @var Token $Token */
        $token = $this->repository->find((string) $uiid);

        if ($token  === null) {
            throw new TokenNotFoundException();
        }
        return $token ;
    }

     /**
     * {@inheritdoc}
     */
    public function generateToken(string $uiid, string $id): string
    {
        
        //if user exist in token table:
        $token = $this->repository->findOneBy(['uiid' => $uiid]);
        
        $hash = hash('sha256', bin2hex(random_bytes(26)));

        $now = time();
        $expiration = $now+86400;
        
        if ($token  === null) {//create new token:
            $token = new Token($id, $uiid, $hash, $now, $expiration);
        }else{//update token:
            $token->setToken($hash);
            $token->setCreated($now);
            $token->setExpiration($expiration);
        }

        $this->entity_manager->persist($token);
        $this->entity_manager->flush();

        return $hash;
    }
    
}