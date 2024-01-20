<?php

declare(strict_types=1);

namespace App\Parser;

use App\Parser\Provider\AlloUaProvider;
use App\Parser\Provider\Contract\ProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Parser
{
    private const PROVIDERS = [
        AlloUaProvider::class => 'allo_ua_products.csv'
    ];

    private array $errors = [];

    public function __construct(
        protected HttpClientInterface $client,
        protected SerializerInterface $serializer,
        protected Filesystem $filesystem,
        protected int $pagesLimit,
        protected string $projectDir
    ) {
        //
    }

    public function run(): array
    {
        foreach (self::PROVIDERS as $provider => $outputFilename) {
            /** @var ProviderInterface $provider */
            $provider = new $provider($this->client, $this->pagesLimit);
            $filepath = $this->projectDir.'/'.$outputFilename;
            if ($this->filesystem->exists($filepath)) {
                $this->filesystem->remove($filepath);
            }

            try {
                foreach ($provider->iteratePages() as $pageData) {
                    $csvContent = $this->serializer->encode($pageData, 'csv', [CsvEncoder::NO_HEADERS_KEY => true]);

                    $this->filesystem->appendToFile($filepath, $csvContent);
                }
            } catch (ParserException $e) {
                $this->errors[] = $e->getMessage();
                continue;
            }
        }

        return $this->errors;
    }
}