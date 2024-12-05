<?php

declare(strict_types=1);

namespace App\Service;

class UrlShortener
{
    public function generateShortCode(): string
    {
        return substr(md5(uniqid()), 0, 5);
    }
}