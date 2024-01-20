<?php

declare(strict_types=1);

namespace App\Parser\Provider;

use App\Parser\Provider\Contract\ProviderInterface;
use Symfony\Component\DomCrawler\Crawler;

final class AlloUaProvider extends AbstractProvider implements ProviderInterface
{
    private const URL = 'https://allo.ua/ua/products/mobile/klass-kommunikator_smartfon/p-%d/';

    protected function parsePageData(string $pageContent): array
    {
        $data = [];
        $crawler = new Crawler($pageContent);
        $elements = $crawler->filterXPath('//div[contains(@class, "products-layout__item")]//div[@class="product-card"]');
        if ($elements->count() === 0) {
            $this->throwException('No elements found. Is DOM changed?');
        }

        foreach ($elements as $element) {
            $element = new Crawler($element);
            $anchor = $element->filterXPath('//div[@class="product-card__img"]//a');
            $data[] = [
                'Name' => $anchor->attr('title'),
                'Image' => $anchor->filterXPath('//img')->attr('src'),
                'Link' => $anchor->attr('href'),
                'Price' => $element->filterXPath('//div[@class="product-card__content"]//div[contains(@class, "v-pb__cur")]')
                    ->text()
            ];
        }

        return $data;
    }

    protected function getUrl(): string
    {
        return self::URL;
    }

    protected function getHeaders(): array
    {
        return [['Name', 'Image', 'Link', 'Price']];
    }
}