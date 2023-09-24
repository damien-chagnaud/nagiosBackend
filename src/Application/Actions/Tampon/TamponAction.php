<?php

declare(strict_types=1);

namespace App\Application\Actions\Tampon;

use App\Application\Actions\Action;
use App\Domain\Tampon\TamponRepository;
use Psr\Log\LoggerInterface;

abstract class TamponAction extends Action
{
    protected TamponRepository $tamponRepository;

    public function __construct(LoggerInterface $logger, TamponRepository $tamponRepository)
    {
        parent::__construct($logger);
        $this->tamponRepository = $tamponRepository;
    }
}
