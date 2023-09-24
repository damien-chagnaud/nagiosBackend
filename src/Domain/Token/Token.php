<?php 

declare(strict_types=1);

namespace App\Domain\Token;

use JsonSerializable;

class Token implements JsonSerializable
{
    private string $id;
    private string $uiid;
    private string $token;
    private int $created;
    private int $expiration;

    public function __construct(string $id, string $uiid, string $token, int $created, int $expiration)
    {
        $this->id = $id;
        $this->uiid = $uiid;
        $this->token = $token;
        $this->created = $created;
        $this->expiration = $expiration;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUiid(): string
    {
        return $this->uiid;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCreated(): int
    {
        return $this->create;
    }

    public function getExpiration(): int
    {
        return $this->expiration;
    }

    //SETTERs
    public function setToken(string $value)
    {
        $this->token = $value;
    }

    public function setCreated(int $value)
    {
        $this->create = $value;
    }

    public function setExpiration(int $value)
    {
        $this->expiration = $value;
    }


    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'uiid' => $this->uiid,
            'token' => $this->token,
            'created' => $this->created,
            'expiration' => $this->expiration
        ];
    }
}