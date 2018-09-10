<?php

namespace Kyoushu\NorthDevonGovData\Scraper;

use GuzzleHttp\Psr7\Request;
use Kyoushu\NorthDevonGovData\Model\BinCollection;
use Kyoushu\NorthDevonGovData\Scraper\Exception\ScraperException;
use Psr\Http\Message\RequestInterface;
use function Sodium\add;
use Symfony\Component\DomCrawler\Crawler;

class BinCollectionScraper extends AbstractHtmlScraper
{

    /**
     * @var string|null
     */
    protected $postcode;

    /**
     * @var string|null
     */
    protected $address;

    /**
     * @var \DateTimeInterface|null
     */
    protected $now;

    public function setNow(\DateTimeInterface $now = null): self
    {
        $this->now = $now;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @throws ScraperException
     */
    protected function assertHasPostcode()
    {
        if($this->postcode) return;
        throw new ScraperException(sprintf('Postcode has not been defined'));
    }

    /**
     * @throws ScraperException
     */
    protected function assertHasAddress()
    {
        if($this->address) return;
        throw new ScraperException(sprintf('Address has not been defined'));
    }

    /**
     * @return RequestInterface
     * @throws ScraperException
     */
    protected function createAddressChoicesRequest(): RequestInterface
    {
        $this->assertHasPostcode();
        $postcode = $this->getPostcode();
        $uri = sprintf(self::BASE_URI . '/bins-and-recycling/collection-dates/bin-collection-results/?DS=130&id=%s', urlencode($postcode));
        return new Request('GET', $uri);
    }

    /**
     * @return array
     * @throws ScraperException
     * @throws \Http\Client\Exception
     */
    public function getAddressChoices(): array
    {
        $choices = [];

        $crawler = $this->createCrawler($this->createAddressChoicesRequest());
        $links = $crawler->filter('#main a[href^="/bins-and-recycling/collection-dates/bin-collection-results"]');

        foreach($links as $link){
            $href = $link->getAttribute('href');

            if(!preg_match('/ID=(?<id>[0-9]+)/', $href, $match)) continue;
            $id = (int)$match['id'];

            $choices[] = [
                'address' => self::getNodeText($link),
                'uri' => self::BASE_URI . $href,
                'id' => $id
            ];

        }

        return $choices;
    }

    /**
     * @return int
     * @throws ScraperException
     * @throws \Http\Client\Exception
     */
    public function resolveDetailsUri(): string
    {
        $this->assertHasAddress();
        $address = $this->getAddress();

        if(preg_match('/^[0-9]+$/', $address)) $regex = sprintf('/^%s /i', preg_quote($address, '/'));
        else $regex = sprintf('/^%s/i', preg_quote($address, '/'));

        $choices = $this->getAddressChoices();

        foreach($choices as $choice){
            if(preg_match($regex, $choice['address'])) return $choice['uri'];
        }

        throw new ScraperException(sprintf(
            'Unable to resolve id for "%s" at postcode "%s"',
            $address,
            $this->getPostcode()
        ));

    }

    public function createDetailsRequest(): RequestInterface
    {
        return new Request('GET', $this->resolveDetailsUri());
    }


    /**
     * @return BinCollection[]|array
     * @throws ScraperException
     * @throws \Http\Client\Exception
     * @throws \Exception
     */
    public function getData()
    {
        $collections = [];

        $this->assertHasHttpClient();

        $response = $this->getHttpClient()->sendRequest($this->createDetailsRequest());
        $crawler = new Crawler($response->getBody()->getContents());

        $elements = $crawler->filter('#main ul')->first()->filter('li');

        foreach($elements as $element){
            $text = self::getNodeText($element);
            $date = self::extractDate($text, $this->now);

            if($date === null) continue;

            $type = null;
            if(preg_match('/black bin/i', $text)) $type = BinCollection::TYPE_BLACK_BIN;
            elseif(preg_match('/green bin/i', $text)) $type = BinCollection::TYPE_GREEN_BIN;
            elseif(preg_match('/recycling/i', $text)) $type = BinCollection::TYPE_RECYCLING;

            if($type === null) continue;
            if(isset($collections[$type])) continue; // Don't overwrite existing matches

            $collections[$type] = new BinCollection($type, $text, $date);
        }

        usort($collections, function(BinCollection $a, BinCollection $b){
            return ($a->getDate() > $b->getDate() ? 1 : -1);
        });

        return array_values($collections);
    }

    /**
     * @return BinCollection[]|array
     * @throws ScraperException
     * @throws \Http\Client\Exception
     */
    public function getBinCollections(): array
    {
        return $this->getData();
    }

    public function getNextBinCollection(): ?BinCollection
    {
        $collections = $this->getBinCollections();
        if(count($collections) === 0) return null;
        return $collections[0];
    }

}