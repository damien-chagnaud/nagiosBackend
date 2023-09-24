<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Tampon;

use App\Domain\Tampon\Tampon;
use App\Domain\Tampon\TamponNotFoundException;
use App\Domain\Tampon\TamponRepository;

class InMemoryTamponRepository implements TamponRepository
{
    /**
     * @var Tampon[]
     */
    private array $tampons;

    /**
     * @param Tampon[]|null $tampons
     */
    public function __construct(array $tampons = null)
    {
        $this->tampons = $tampons ?? [
            1 => new Tampon(1, 'Trodat 4910', 'Printy 4910', 1),
            2 => new Tampon(1, 'Trodat 4912', 'Printy 4912', 15),
            3 => new Tampon(1, 'Trodat 4913', 'Printy 4913', 4),
            4 => new Tampon(1, 'Trodat 4914', 'Printy 4914', 8),
            5 => new Tampon(1, 'Trodat 4915', 'Printy 4915', 5),
            6 => new Tampon(1, 'Trodat 4916', 'Printy 4916', 4),
            7 => new Tampon(1, 'Trodat 4917', 'Printy 4917', 3),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->tampons);
    }

    /**
     * {@inheritdoc}
     */
    public function findTamponOfId(int $id): Tampon
    {
        if (!isset($this->tamponss[$id])) {
            throw new TamponNotFoundException();
        }

        return $this->tampons[$id];
    }


}
