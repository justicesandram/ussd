<?php
namespace TNM\USSD\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use TNM\USSD\Models\TransactionTrail;
use TNM\USSD\Contracts\TransactionTrailStorageInterface;


class CacheTransactionTrailStorage implements TransactionTrailStorageInterface
{
    private function getStore()
    {
        return Cache::store(config('ussd.storage.cache_store', 'file'));
    }
    
    private function getSessionKey(string $sessionId): string
    {
        return "ussd:transaction_trail:{$sessionId}";
    }
    
    public function add(string $sessionId, string $message, string $response): TransactionTrail
    {
        $transactionTrail = new TransactionTrail([
            'session_uid' => $sessionId,
            'message' => $message,
            'response' => $response,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $transactionTrail->id = uniqid();
        
        $this->storeTransactionTrail($transactionTrail);
        
        return $transactionTrail;
    }
    
    public function findBySession(string $sessionId): Collection
    {
        $data = $this->getStore()->get($this->getSessionKey($sessionId), []);
        
        return collect($data)->map(function ($item) {
            return $this->arrayToTransactionTrail($item);
        });
    }
    
    private function storeTransactionTrail(TransactionTrail $transactionTrail): void
    {
        $sessionKey = $this->getSessionKey($transactionTrail->session_uid);
        $existingData = $this->getStore()->get($sessionKey, []);
        
        $existingData[] = $transactionTrail->toArray();
        
        $this->getStore()->forever($sessionKey, $existingData);
    }
    
    private function arrayToTransactionTrail(array $data): TransactionTrail
    {
        $transactionTrail = new TransactionTrail();
        $transactionTrail->forceFill($data);
        $transactionTrail->exists = true;
        
        return $transactionTrail;
    }
}
