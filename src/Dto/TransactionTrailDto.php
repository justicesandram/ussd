<?php
namespace TNM\USSD\Dto;

final class TransactionTrailDto extends AbstractDto
{
    public function __construct(
        public int $sessionId,
        public string $message,
        public string $response
    ) {
    }
}