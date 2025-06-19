<?php
namespace TNM\USSD\Dto;

use TNM\USSD\Dto\AbstractDto;

final class SessionNumberDto extends AbstractDto
{
    public function __construct(
        public string $msisdn,
        public string $linkedSessionKey,
        public int $sessionId,
        public string $lastScreen
    ) {
    }
}