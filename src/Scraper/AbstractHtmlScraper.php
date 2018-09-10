<?php

namespace Kyoushu\NorthDevonGovData\Scraper;

use Http\Client\HttpClient;
use Kyoushu\NorthDevonGovData\Scraper\Exception\ScraperException;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractHtmlScraper implements ScraperInterface
{

    const BASE_URI = 'https://www.northdevon.gov.uk';

    const REGEX_PARTIAL_DAY = '(Mon(day)?|Tues?(day)?|Wed(s|nesday)?|Thu(rs?(day)?)?|Fri(day)?|Sat(urday)?|Sun(day)?)';
    const REGEX_PARTIAL_DATE = '([0-9]+(st|nd|rd|th)?)';
    const REGEX_PARTIAL_MONTH = '(Jan(uary)?|Feb(ruary)?|Mar(ch)?|Apr(il)?|May|June?|July?|Aug(ust)?|Sept?(ember)?|Oct(ober)?|Nov(ember)?|Dec(ember)?)';

    public static function getNodeText(\DOMElement $element): string
    {
        $text = (string)$element->nodeValue;
        $text = str_replace("\n", ' ', $text);
        $text = preg_replace('/\\s+/', ' ', $text);
        return trim($text);
    }

    public static function createDateRegex(): string
    {
        return sprintf(
            '/(?<day>%s)(\s(?<date>%s))?(\s(?<month>%s))?(\s(?<date_end>%s))?/',
            self::REGEX_PARTIAL_DAY,
            self::REGEX_PARTIAL_DATE,
            self::REGEX_PARTIAL_MONTH,
            self::REGEX_PARTIAL_DATE
        );
    }

    /**
     * @param string $text
     * @return \DateTimeInterface|null
     * @throws \Exception
     */
    public static function extractDate(string $text, \DateTimeInterface $now = null): ?\DateTimeInterface
    {
        if($now === null) $now = new \DateTimeImmutable('now');

        $regex = self::createDateRegex();

        if(!preg_match($regex, $text, $match)) return null;

        $date = $match['date'];
        if($date === '') $date = $match['date_end'];
        if(!$date) return null;
        $date = (int)$date;

        $monthPartials = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
        $monthPartial = strtolower(substr($match['month'], 0, 3));
        $monthIndex = array_search($monthPartial, $monthPartials);
        if($monthIndex === false) return null;
        $month = $monthIndex + 1;

        $currentYear = (int)$now->format('Y');

        $year = $currentYear;

        $dateString = sprintf('%s-%s-%s', $year, $month < 10 ? '0' . $month : $month, $date < 10 ? '0' . $date : $date);
        $dateTime = new \DateTimeImmutable($dateString);

        if($dateTime >= $now) return $dateTime;

        $year++;
        $dateString = sprintf('%s-%s-%s', $year, $month < 10 ? '0' . $month : $month, $date < 10 ? '0' . $date : $date);
        return new \DateTimeImmutable($dateString);
    }

    /**
     * @var HttpClient|null
     */
    protected $httpClient;

    /**
     * @return HttpClient|null
     */
    public function getHttpClient(): ?HttpClient
    {
        return $this->httpClient;
    }

    /**
     * @throws ScraperException
     */
    protected function assertHasHttpClient()
    {
        if($this->httpClient) return;
        throw new ScraperException(sprintf(
            'An instance of %s must be set in %s using the method setHttpClient()',
            HttpClient::class,
            static::class
        ));
    }

    /**
     * @param HttpClient $httpClient
     * @return $this
     */
    public function setHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return Crawler
     * @throws \Http\Client\Exception
     * @throws ScraperException
     */
    public function createCrawler(RequestInterface $request): Crawler
    {
        $this->assertHasHttpClient();
        $httpClient = $this->getHttpClient();
        $response = $httpClient->sendRequest($request);
        return new Crawler($response->getBody()->getContents());
    }

}