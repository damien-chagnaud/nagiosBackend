<?php

declare(strict_types=1);

#Token:
use App\Domain\Token\TokenRepository;
use App\Infrastructure\Persistence\Token\DatabaseTokenRepository;

#User:
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\User\DatabaseUserRepository;

#Tampon:
use App\Domain\Tampon\TamponRepository;
use App\Infrastructure\Persistence\Tampon\DatabaseTamponRepository;

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
 
    $containerBuilder->addDefinitions([
        TokenRepository::class => \DI\autowire(DatabaseTokenRepository::class),
        UserRepository::class => \DI\autowire(DatabaseUserRepository::class),
        TamponRepository::class => \DI\autowire(DatabaseTamponRepository::class),
    ]);
};
