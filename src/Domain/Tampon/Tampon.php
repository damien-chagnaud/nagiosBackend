<?php 

declare(strict_types=1);

namespace App\Domain\Tampon;

use JsonSerializable;

class Tampon implements JsonSerializable
{
    private string $id;
    private string $type;
    private string $name;
    private string $ref;
    private string $brand;
    private int $instock;

    public function __construct(string $id, string $type, string $name, string $ref, string $brand, int $instock)
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->ref = $ref;
        $this->brand = $brand;
        $this->instock = $instock;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getInstock(): int
    {
        return $this->instock;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'ref' => $this->ref,
            'brand' => $this->brand,
            'instock' => $this->instock,
        ];
    }
}