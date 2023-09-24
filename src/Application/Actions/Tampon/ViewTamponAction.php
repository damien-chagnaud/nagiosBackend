<?php

declare(strict_types=1);

namespace App\Application\Actions\Tampon;

use Psr\Http\Message\ResponseInterface as Response;

class ViewTamponAction extends TamponAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $tampon_id = $this->resolveArg('id');

        $tampon = $this->tamponRepository->findTamponOfId(intval($tampon_id));

        $this->logger->info(sprintf("Tampon of  `%s` was viewed.", $tampon_id));

        return $this->respondWithData($tampon);
    }
}
