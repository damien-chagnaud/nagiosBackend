<?php declare(strict_types=1);

namespace App\Application\Actions\Tampon;

use App\Domain\Tampon\Tampon;
use App\Domain\GenericId;
use Assert\Assert;
use function uuid_create;
use Psr\Http\Message\ResponseInterface as Response;

class NewTamponAction extends TamponAction
{ 
    /**
    * {@inheritdoc}
    */
   protected function action(): Response
   {
    //?int $id, string $type, string $name, string $ref, string $brand, int $instock

       $data = (array) $this->request->getParsedBody();
       $uuid = uuid_create(UUID_TYPE_RANDOM);

       $this->logger->info('User_Id: '.$uuid);

       $this->logger->info(print_r($uuid, true));

       Assert::lazy()
       ->that($data)
       ->keyExists('type')
       ->that($data)
       ->keyExists('name')
       ->that($data)
       ->keyExists('ref')
       ->that($data)
       ->keyExists('brand')
       ->that($data)
       ->keyExists('instock')
       ->verifyNow();

       Assert::lazy()
       ->that($data['type'])
       ->notEmpty()
       ->that($data['name'])
       ->notEmpty()
       ->that($data['ref'])
       ->notEmpty()
       ->that($data['brand'])
       ->notEmpty()
       ->that($data['instock'])
       ->notEmpty()
       ->verifyNow();

       $this->logger->info('Create tampon');
       $tampon = new Tampon(
        $uuid,
        $data['type'],
        $data['name'],
        $data['ref'],
        $data['brand'],
        intval($data['instock']),
       );

       $this->logger->info('Add tampon');
       $this->tamponRepository->add($tampon);

       $this->logger->info('Tampon was created. with:'.$uuid);

       return $this->respondWithData($tampon, 201);
   }
}
