<?php

namespace TNM\USSD\Http\Flares;

use TNM\USSD\Http\Request;
use TNM\USSD\Http\UssdRequestInterface;
use TNM\USSD\Repositories\Database\EloquentSessionRepository;

class FlaresRequest implements UssdRequestInterface
{
    private mixed $request;

    public function __construct()
    {
        $this->request = json_decode(json_encode(
            simplexml_load_string(request()->getContent())
        ), true);
    }

    public function getMsisdn(): ?string
    {
        return $this->request['msisdn'] ?? null;
    }

    public function getSession(): ?string
    {
        return $this->request['sessionId'] ?? null;
    }

    public function getType(): int
    {
        return EloquentSessionRepository::findBySessionUid($this->getSession())
            ? Request::RESPONSE
            : Request::INITIAL;
    }

    public function getMessage(): ?string
    {
        return $this->request['subscriberInput'] ?? null;
    }
}
