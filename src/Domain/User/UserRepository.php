<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRepository
{

     /**
     * @param ivoid
     * @return string
     * @throws UserNotFoundException
     */
    public function findUserByName(string $username): User;

    /**
     * @param ivoid
     * @return string
     * @throws UserNotFoundException
     */
    public function findUserByUiid(string $uiid): User;
}
