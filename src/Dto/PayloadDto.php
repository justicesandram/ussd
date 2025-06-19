<?php
namespace TNM\USSD\Dto;

use TNM\USSD\Dto\AbstractDto;

final class PayloadDto extends AbstractDto
{
    public function __construct(
        public ?string $payloadId = null,
        public int $sessionId,
        public string $key,
        public string $value
    ) {
    }
}