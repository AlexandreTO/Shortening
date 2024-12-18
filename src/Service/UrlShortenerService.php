<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;

class UrlShortenerService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function shortenUrl(string $orignalUrl): string
    {
        $existingUrl = $this->em->getRepository(Url::class)->findOneBy(['originalUrl' => $orignalUrl]);
        if ($existingUrl) {
            return $existingUrl->getShortenedUrl();
        }

        $shortCode = $this->generateShortCode();

        $url = new Url();
        $url->setOriginalUrl($orignalUrl);
        $url->setShortenedUrl($shortCode);
        $url->setCreatedAt(new \DateTimeImmutable());
        
        $this->em->persist($url);
        $this->em->flush();

        return $shortCode;
    }

    public function redirectUrl(string $shortenedUrl): string
    {
        $shortCode = $this->em->getRepository(Url::class)->findOneBy(["shortenedUrl"=> $shortenedUrl]);
        if (!$shortCode) {
            throw new \Exception("Shortened URL not found.");
        }

        $shortCode->incrementHits();
        $this->em->flush();

        return $shortCode->getOriginalUrl();
    }

    private function generateShortCode(): string
    {
        return substr(md5(uniqid()), 0, 5);
    }
}