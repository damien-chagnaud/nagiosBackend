<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    private string $id;

    private string $username;

    private string $password;

    private string $firstname;

    private string $lastname;

    private string $email;

    private string $uidkey;

    private int $level;

    public function __construct(string $id, string $username, string $password, string $firstname, string $lastname, string $email, string $uidkey, int $level)
    {
        $this->id = $id;
        $this->username = strtolower($username);
        $this->password = $password;
        $this->firstname = ucfirst($firstname);
        $this->lastname = ucfirst($lastname);
        $this->email = $email;
        $this->uidkey = strtolower($uidkey);
        $this->level = $level;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUidkey(): string
    {
        return $this->uidkey;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setUidkey(string $uidkey): string
    {
        return $this->uidkey = $uidkey;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'uidkey' => $this->uidkey,
            'level' => $this->level,
        ];
    }
}
