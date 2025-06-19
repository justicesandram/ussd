<?php
namespace TNM\USSD\Dto;

use DateTimeImmutable;
use JsonSerializable;

abstract class AbstractDto implements JsonSerializable
{
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTimeImmutable $updatedAt = null;

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
    public static function generateKey(int $keyLength = 6): string
    {
        return bin2hex(string: random_bytes(length: $keyLength));
    }
}