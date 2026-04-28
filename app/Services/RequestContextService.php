<?php

namespace App\Services;

class RequestContextService
{
    /** @return array{ip_address: string|null, user_agent: string|null} */
    public function current(): array
    {
        return [
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ];
    }
}
