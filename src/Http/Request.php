<?php

namespace TNM\USSD\Http;

use Illuminate\Http\Request as BaseRequest;
use TNM\USSD\Factories\RequestFactory;
use TNM\USSD\Models\Session;
use TNM\USSD\Repositories\Database\EloquentSessionRepository;
use TNM\USSD\Screen;

class Request extends BaseRequest
{
    const INITIAL = 1, RESPONSE = 2, RELEASE = 3, TIMEOUT = 4;
    public string $msisdn = '';
    public ?string $sessionUid;
    public int $type;
    public string $message;
    public Session $trail;
    private UssdRequestInterface $ussdRequest;
    private ?EloquentSessionRepository $sessionRepository = null;

    public function __construct()
    {
        parent::__construct();
        $this->ussdRequest = (new RequestFactory())->make();

        if ($this->isInvalid())
            return;
        $this->setRequestProperties()->setSessionLocale()->setSessionTrail();
    }

    private function provideRepository(): EloquentSessionRepository
    {
        if (null === $this->sessionRepository) {
            $this->sessionRepository = new EloquentSessionRepository();
        }
        return $this->sessionRepository;
    }
    public function toPreviousScreen(): bool
    {
        return $this->message == config('ussd.navigation.previous');
    }


    public function navigatingHome(): bool
    {
        return $this->message == config('ussd.navigation.home');
    }

    public function toHomeScreen(): bool
    {
        return $this->isInitial() || !$this->getExistingSession();
    }

    public function isInvalid(): bool
    {
        return empty($this->ussdRequest->getMsisdn()) ||
            empty($this->ussdRequest->getSession()) ||
            empty($this->ussdRequest->getType()) ||
            empty($this->ussdRequest->getMessage());
    }

    private function setRequestProperties(): self
    {
        $this->msisdn = $this->ussdRequest->getMsisdn();
        $this->sessionUid = $this->ussdRequest->getSession();
        $this->type = $this->ussdRequest->getType();
        $this->message = $this->ussdRequest->getMessage();
        return $this;
    }

    private function setSessionLocale(): self
    {
        if (empty($this->sessionUid) || $this->provideRepository()::notCreated($this->sessionUid))
            return $this;

        $session = $this->provideRepository()::findBySessionUid($this->sessionUid);
        app()->setLocale($session->{'locale'});
        return $this;
    }

    public function isInitial(): bool
    {
        return $this->type == self::INITIAL;
    }

    public function isResponse(): bool
    {
        return $this->type == self::RESPONSE;
    }

    public function isTimeout(): bool
    {
        return $this->type == self::TIMEOUT;
    }

    public function isReleased(): bool
    {
        return $this->type == self::RELEASE;
    }

    public function isNotUserResponse(): bool
    {
        return $this->isInitial() || $this->isTimeout() || $this->isReleased();
    }

    public function isNotReleased(): bool
    {
        return !$this->isReleased();
    }

    public function isNotTimeout(): bool
    {
        return !$this->isTimeout();
    }

    private function getTrail(): Session
    {
        $existingSession = $this->getExistingSession();
        if ($existingSession) {
            return $this->provideRepository()->updateSessionUid($existingSession, $this->sessionUid);
        }

        $session = $this->provideRepository()::findBySessionUid($this->sessionUid);
        if ($session) {
            return $session;
        }

        return $this->provideRepository()::track(
            sessionUid: $this->sessionUid,
            state: config('ussd.routing.landing_screen'),
            msisdn: $this->msisdn
        );
    }

    public function getScreen(): Screen
    {
        return new $this->trail->{'state'}($this);
    }

    public function getPreviousScreen(): Screen
    {
        return $this->getScreen()->previous();
    }

    public function getExistingSession(): ?Session
    {
        return $this->provideRepository()::recentSessionByPhone($this->msisdn);
    }

    private function setSessionTrail(): void
    {
        $this->trail = $this->getTrail();
    }
}
