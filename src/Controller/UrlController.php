<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UrlShortenerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    private UrlShortenerService $urlShortenerService;

    public function __construct(UrlShortenerService $urlShortenerService)
    {
        $this->urlShortenerService = $urlShortenerService;
    }

    #[Route('/shorten', name: 'url_shorten', methods: ['POST'])]
    public function shorten(Request $request): JsonResponse
    {
        $originalUrl = $request->get("url");

        if (!$originalUrl) {
            return new JsonResponse(['error' => 'No URL provided'], Response::HTTP_BAD_REQUEST);
        }

        $shortcode = $this->urlShortenerService->shortenUrl($originalUrl);

        return new JsonResponse(['success' => true, 'shortCode' => $shortcode]);
    }

    #[Route('/{shortCode}', name: 'redirect', methods: ['GET'])]
    public function redirectToOriginaUrl(string $shortcode): RedirectResponse
    {
        try {
            $originalUrl = $this->urlShortenerService->redirectUrl($shortcode);
            return $this->redirect($originalUrl);
        } catch (\Throwable $th) {
            throw $this->createNotFoundException('The requested URL was not found.');
        }
    } 
}