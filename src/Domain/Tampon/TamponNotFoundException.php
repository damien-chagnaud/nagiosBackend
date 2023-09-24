<?php declare(strict_types=1);

namespace App\Tampon\Domain;

use App\Common\Domain\DomainException\DomainRecordNotFoundException;
use Throwable;

class TamponNotFoundException extends DomainRecordNotFoundException
{
    public function __construct(
        $message = 'The stamp you requested does not exist.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}