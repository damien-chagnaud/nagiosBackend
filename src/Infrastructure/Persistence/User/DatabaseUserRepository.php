<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use function uuid_create;

class DatabaseUserRepository implements UserRepository
{

    private EntityRepository $repository;
    private EntityManager $entity_manager;

    public function __construct(EntityManager $entity_manager)
    {
        $this->repository = $entity_manager->getRepository(User::class);
        $this->entity_manager = $entity_manager;
    }

     /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

     /**
     * {@inheritdoc}
     */
    public function findUserByUiid(string $uiid): User
    {
        /** @var User $user */
        $user = $this->repository->find((string) $uiid);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function findUserByName(string $username): User
    {
        /** @var User $user */
        $user = $this->repository->findOneBy(['username' => $username]);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }
    
    public function update(User $user): void
    {
        $this->entity_manager->persist($user);
        $this->entity_manager->flush();

    }

    public function delete(User $user): void
    {
        $this->entity_manager->remove($user);
        $this->entity_manager->flush();
    }

}