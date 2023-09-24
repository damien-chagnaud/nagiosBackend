<?php declare(strict_types=1);

namespace App\Application\Actions\Tampon;

use Psr\Http\Message\ResponseInterface as Response;


class UpdateTamponAction extends TamponAction
{ 
    
    /**
    * {@inheritdoc}
    */
   protected function action(): Response
   {
        $data = (array) $this->request->getParsedBody();
        $tampon_id = $this->resolveArg('id');

        $tampon = $this->tamponRepository->findTamponOfId(intval($tampon_id));


        if (!$tampon) {
            $this->logger->error(sprintf("Tampon of  `%s` not found.", $tampon_id));
        }else{
            if(isset($data['instock'])) $tampon->setInstock(intval($data['instock']));

            $this->tampon_repository->flush();
            $this->logger->info(sprintf("Tampon of  `%s` was updated.", $tampon_id));
        }

        return $this->respondWithData($tampon, 201);
   }
}
