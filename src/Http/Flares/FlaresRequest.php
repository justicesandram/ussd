<?php

namespace TNM\USSD\Http\Flares;

use TNM\USSD\Http\Request;
use TNM\USSD\Storage\StorageManager;
use TNM\USSD\Http\UssdRequestInterface;
use TNM\USSD\Contracts\SessionStorageInterface;
use TNM\USSD\Repositories\Database\EloquentSessionRepository;

class FlaresRequest implements UssdRequestInterface
{
    private mixed $request;
    private ?SessionStorageInterface $sessionStorage = null;

    public function __construct()
    {
        $this->request = json_decode(json_encode(
            simplexml_load_string(request()->getContent())
        ), true);

        if (null === $this->sessionStorage) {
            $this->sessionStorage = (new StorageManager())->sessionStorage();
        }
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
        return $this->sessionStorage->findBySessionId($this->getSession())
            ? Request::RESPONSE
            : Request::INITIAL;
    }

    public function getMessage(): ?string
    {
        return $this->request['subscriberInput'] ?? null;
    }
}
