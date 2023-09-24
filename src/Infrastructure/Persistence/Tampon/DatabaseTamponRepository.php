<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Tampon;

use App\Domain\Tampon\Tampon;
use App\Domain\Tampon\TamponNotFoundException;
use App\Domain\Tampon\TamponRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;


class DatabaseTamponRepository implements TamponRepository
{

    private EntityRepository $repository;
    private EntityManager $entity_manager;

    public function __construct(EntityManager $entity_manager)
    {
        $this->repository = $entity_manager->getRepository(Tampon::class);
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
    public function findTamponOfId(int $id): Tampon
    {
        /** @var Tammpon $tampon */
        $tampon = $this->repository->find((int) $id);

        if ($tampon  === null) {
            throw new TamponNotFoundException();
        }

        return $tampon ;
    }

    public function add(Tampon $tampon): void
    {
        $this->entity_manager->persist($tampon);
        $this->entity_manager->flush();
    }

    public function update(Tampon $tampon): void
    {
        $this->entity_manager->persist($tampon);
        $this->entity_manager->flush();
    }





}