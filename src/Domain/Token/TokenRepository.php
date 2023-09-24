<?php

declare(strict_types=1);

namespace App\Domain\Token;

interface TokenRepository
{
    /**
     * @throws TokenNotFoundException
     */
    public function findTokenOfUiid(string $uiid): Token;

    /**
     * @throws TokenNotFoundException
     */
    public function generateToken(string $uiid, string $id): string;

}