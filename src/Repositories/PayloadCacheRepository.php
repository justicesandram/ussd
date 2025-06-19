<?php
namespace TNM\USSD\Repositories;

use DateTimeImmutable;
use Exception;
use TNM\USSD\Dto\PayloadDto;
use Illuminate\Support\Facades\Cache;

final class PayloadCacheRepository
{
    private const MAP_KEY = 'payload_map';

    public function create(PayloadDto $data): PayloadDto
    {
        if ($data->payloadId === null) {
            $data->payloadId = $data::generateKey();
        }
        if ($data->createdAt === null) {
            $data->createdAt = new DateTimeImmutable();
        }

        if (!Cache::forever($data->payloadId, $data)) {
            throw new Exception("Failed to create payload");
        }
        $payloadIdMap = Cache::rememberForever('payload_id_map', function () {
            return [];
        });

        if (!in_array($data->payloadId, $payloadIdMap, true)) {
            $payloadIdMap[] = $data->payloadId;
            Cache::forever('payload_id_map', $payloadIdMap);
        }

        return $data;


    }

    public static function findBySession(int $sessionId): array
    {

    }

}
