<?php

declare(strict_types=1);

namespace App\Application\Actions\Tampon;

use Psr\Http\Message\ResponseInterface as Response;

class ListTamponAction extends TamponAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $tampons = $this->tamponRepository->findAll();

        $this->logger->info("Tampon list was viewed.");
    

        return $this->respondWithData($tampons);
    }
}
