# north-devon-gov-data
A library for extracting data from northdevon.gov.uk

## Usage

### Bin Collection

```php
$scraper = new \Kyoushu\NorthDevonGovData\Scraper\BinCollectionScraper();
$scraper->setHttpClient($httpClient);
$scraper->setPostcode('AB12 3CD');
$scraper->setAddress('32 Example Street');
$binCollection = $scraper->getNextBinCollection();

echo $binCollection->getDate()->format('l jS F Y') . ' - ' . $binCollection->getType(); // E.g. "Monday 10th September 2018 - Black Bin"
```