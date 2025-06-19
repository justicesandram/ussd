<?php
namespace TNM\USSD\Dto;

final class SessionDto extends AbstractDto
{
    public function __construct(
        public ?string $sessionKey = null,
        public ?int $sessionId = null,
        public ?string $state = null,
        public ?string $payload = null,
        public ?string $locale = null,
        public ?string $msisdn = null
    ) {
    }


}