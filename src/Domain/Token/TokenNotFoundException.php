<?php declare(strict_types=1);

namespace App\Token\Domain\Token;

use App\Common\Domain\DomainException\DomainRecordNotFoundException;
use Throwable;

class TokenNotFoundException extends DomainRecordNotFoundException
{
    public function __construct(
        $message = 'The token you requested does not exist.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}