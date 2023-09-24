<?php

declare(strict_types=1);

namespace App\Domain\Tampon;

interface TamponRepository
{
    /**
     * @return Tampon[]
     */
    public function findAll(): array;

    /**
     * @throws TamponNotFoundException
     */
    public function findTamponOfId(int $id): Tampon;

    public function add(Tampon $tampon): void;


}