<?php

declare(strict_types=1);

namespace App\Parser\Provider;

use App\Parser\ParserException;
use App\Parser\Provider\Contract\ProviderInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractProvider implements ProviderInterface
{
    public function __construct(
        protected HttpClientInterface $client,
        protected int $pagesLimit
    ) {
        //
    }

    public function iteratePages(): \Generator
    {
        $currentPage = 1;

        while ($currentPage++ <= $this->pagesLimit) {
            $response = $this->loadPage(sprintf($this->getUrl(), $currentPage));
            try {
                yield $this->parsePageData($response->getContent());
            } catch (ServerExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | TransportExceptionInterface $e) {
                $this->throwException('Error while loading page content', $e->getMessage());
            }
        }
    }

    protected function loadPage(string $url): ResponseInterface
    {
        try {
            return $this->client->request('GET', $url);
        } catch (TransportExceptionInterface $e) {
            $this->throwException('Cannot load page', $e->getMessage());
        }
    }

    protected function throwException(string $message, ?string $originalMessage = null): never
    {
        $message = $this::class.': '.$message;
        if ($originalMessage !== null) {
            $message .= '. Original error message: '.$originalMessage;
        }

        throw new ParserException($message);
    }

    abstract protected function parsePageData(string $pageContent): array;
    abstract protected function getUrl(): string;

}