<?php

namespace TNM\USSD\Services;

interface CleanUpServiceInterface
{
    /**
     * Archive (optionally) and delete records older than given minutes.
     *
     * @param int  $minutes
     * @param bool $archive
     * @return array<string,int>  // [ 'sessions' => 123, ... ]
     */
    public function cleanup(int $minutes, bool $archive): array;
}