<?php

namespace Tests\Kyoushu\NorthDevonGovData\Scraper;

use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use Kyoushu\NorthDevonGovData\Model\BinCollection;
use Kyoushu\NorthDevonGovData\Scraper\BinCollectionScraper;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Kyoushu\NorthDevonGovData\TestCase;

class BinCollectionScraperTest extends TestCase
{



    /**
     * @return MockObject|HttpClient
     */
    protected function createMockHttpClient()
    {
        $httpClient = $this->getMockBuilder(HttpClient::class)->getMock();
        return $httpClient;
    }

    public function testGetAddressChoices()
    {
        $httpClient = $this->createMockHttpClient();

        $httpClient->method('sendRequest')
            ->with( $this->matchRequestURI( 'https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?DS=130&id=AB12+3CD' ) )
            ->willReturn( new Response( 200, [], file_get_contents(__DIR__ . '/../Resources/mock_http/response/bin-collection-addresses.html' ) ) )
        ;

        $scraper = new BinCollectionScraper();
        $scraper->setPostcode('AB12 3CD');
        $scraper->setHttpClient($httpClient);
        $choices = $scraper->getAddressChoices();

        $this->assertCount(4, $choices);

        $this->assertArraySubset([
            [
                "address" => "1 Example Street Town",
                "uri" => "https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?ID=100000000001&DS=140",
                "id" => 100000000001

            ]
        ], $choices);
    }

    public function testResolveAddressId()
    {
        $httpClient = $this->createMockHttpClient();

        $httpClient->method('sendRequest')
            ->with( $this->matchRequestURI( 'https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?DS=130&id=AB12+3CD' ) )
            ->willReturn( new Response( 200, [], file_get_contents(__DIR__ . '/../Resources/mock_http/response/bin-collection-addresses.html' ) ) )
        ;

        $scraper = new BinCollectionScraper();
        $scraper->setPostcode('AB12 3CD');
        $scraper->setHttpClient($httpClient);

        $scraper->setAddress('3 example street');
        $id = $scraper->resolveDetailsUri();

        $this->assertEquals('https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?ID=100000000003&DS=140', $id);
    }

    public function testResolveAddressIdHouseNumber()
    {
        $httpClient = $this->createMockHttpClient();

        $httpClient->method('sendRequest')
            ->with( $this->matchRequestURI( 'https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?DS=130&id=AB12+3CD' ) )
            ->willReturn( new Response( 200, [], file_get_contents(__DIR__ . '/../Resources/mock_http/response/bin-collection-addresses.html' ) ) )
        ;

        $scraper = new BinCollectionScraper();
        $scraper->setPostcode('AB12 3CD');
        $scraper->setHttpClient($httpClient);

        $scraper->setAddress(2);
        $id = $scraper->resolveDetailsUri();

        $this->assertEquals('https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?ID=100000000002&DS=140', $id);
    }

    /**
     * @throws \Http\Client\Exception
     * @throws \Kyoushu\NorthDevonGovData\Scraper\Exception\ScraperException
     */
    public function testGetData()
    {
        $httpClient = $this->createMockHttpClient();

        $httpClient
            ->expects($this->at(0))
            ->method('sendRequest')
            ->with( $this->matchRequestURI( 'https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?DS=130&id=AB12+3CD' ) )
            ->willReturn( new Response( 200, [], file_get_contents(__DIR__ . '/../Resources/mock_http/response/bin-collection-addresses.html' ) ) )
        ;

        $httpClient
            ->expects($this->at(1))
            ->method('sendRequest')
            ->with( $this->matchRequestURI( 'https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?ID=100000000002&DS=140' ) )
            ->willReturn( new Response( 200, [], file_get_contents(__DIR__ . '/../Resources/mock_http/response/bin-collection-details.html' ) ) )
        ;

        $scraper = new BinCollectionScraper();
        $scraper->setPostcode('AB12 3CD');
        $scraper->setHttpClient($httpClient);
        $scraper->setNow(new \DateTimeImmutable('2018-06-01'));

        $scraper->setAddress(2);
        $data = $scraper->getData();

        $this->assertCount(3, $data);

        $this->assertInstanceOf(BinCollection::class, $data[0]);
        $this->assertEquals(BinCollection::TYPE_GREEN_BIN, $data[0]->getType());
        $this->assertEquals('If you’ve registered for garden waste, your next green bin collection will be on Tuesday Sep 11', $data[0]->getText());
        $this->assertEquals('2018-09-11', $data[0]->getDate()->format('Y-m-d'));

        $this->assertInstanceOf(BinCollection::class, $data[1]);
        $this->assertEquals(BinCollection::TYPE_RECYCLING, $data[1]->getType());
        $this->assertEquals('Your next Weekly recycling collection will be Friday Sep 14.', $data[1]->getText());
        $this->assertEquals('2018-09-14', $data[1]->getDate()->format('Y-m-d'));

        $this->assertInstanceOf(BinCollection::class, $data[2]);
        $this->assertEquals(BinCollection::TYPE_BLACK_BIN, $data[2]->getType());
        $this->assertEquals('Your next Fortnightly Black Bin/Bag collection will be Tuesday Sep 18.', $data[2]->getText());
        $this->assertEquals('2018-09-18', $data[2]->getDate()->format('Y-m-d'));
    }

    public function testGetNextBinCollection()
    {
        $httpClient = $this->createMockHttpClient();

        $httpClient
            ->expects($this->at(0))
            ->method('sendRequest')
            ->with( $this->matchRequestURI( 'https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?DS=130&id=AB12+3CD' ) )
            ->willReturn( new Response( 200, [], file_get_contents(__DIR__ . '/../Resources/mock_http/response/bin-collection-addresses.html' ) ) )
        ;

        $httpClient
            ->expects($this->at(1))
            ->method('sendRequest')
            ->with( $this->matchRequestURI( 'https://www.northdevon.gov.uk/bins-and-recycling/collection-dates/bin-collection-results/?ID=100000000002&DS=140' ) )
            ->willReturn( new Response( 200, [], file_get_contents(__DIR__ . '/../Resources/mock_http/response/bin-collection-details.html' ) ) )
        ;

        $scraper = new BinCollectionScraper();
        $scraper->setPostcode('AB12 3CD');
        $scraper->setAddress(2);
        $scraper->setHttpClient($httpClient);
        $scraper->setNow(new \DateTimeImmutable('2018-09-12'));

        $binCollection = $scraper->getNextBinCollection();
        $this->assertInstanceOf(BinCollection::class, $binCollection);

        $this->assertEquals(BinCollection::TYPE_RECYCLING, $binCollection->getType());
        $this->assertEquals('Your next Weekly recycling collection will be Friday Sep 14.', $binCollection->getText());
        $this->assertEquals('2018-09-14', $binCollection->getDate()->format('Y-m-d'));
    }

}