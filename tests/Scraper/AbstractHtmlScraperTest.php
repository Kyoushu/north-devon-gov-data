<?php

namespace Tests\Kyoushu\NorthDevonGovData\Scraper;

use Kyoushu\NorthDevonGovData\Scraper\AbstractHtmlScraper;
use Tests\Kyoushu\NorthDevonGovData\TestCase;

class AbstractHtmlScraperTest extends TestCase
{

    public function testRegexPartialDay()
    {
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Mon');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Monday');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Tue');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Tues');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Tuesday');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Wed');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Weds');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Wednesday');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Thu');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Thur');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Thurs');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Thursday');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Fri');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Friday');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Sat');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Saturday');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Sun');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_DAY . '$/', 'Sunday');
    }

    public function testRegexPartialMonth()
    {
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Jan');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'January');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Feb');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'February');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Mar');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'March');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Apr');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'April');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'May');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Jun');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'June');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Jul');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'July');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Aug');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'August');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Sep');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Sept');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'September');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Oct');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'October');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Nov');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'November');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'Dec');
        $this->assertRegExp('/^' . AbstractHtmlScraper::REGEX_PARTIAL_MONTH . '$/', 'December');
    }

    public function testCreateDateRegex()
    {
        $regex = AbstractHtmlScraper::createDateRegex();

        $text = 'Mon 1 Jan';
        $this->assertRegExp($regex, $text);
        preg_match($regex, $text, $match);
        $this->assertArraySubset(['day' => 'Mon', 'date' => '1', 'month' => 'Jan'], $match);

        $text = 'Tues 2nd Sept';
        $this->assertRegExp($regex, $text);
        preg_match($regex, $text, $match);
        $this->assertArraySubset(['day' => 'Tues', 'date' => '2nd', 'month' => 'Sept'], $match);

        $text = 'Tues Sep 18';
        $this->assertRegExp($regex, $text);
        preg_match($regex, $text, $match);
        $this->assertArraySubset(['day' => 'Tues', 'date_end' => '18', 'month' => 'Sep'], $match);

        $text = 'Friday 16th December';
        $this->assertRegExp($regex, $text);
        preg_match($regex, $text, $match);
        $this->assertArraySubset(['day' => 'Friday', 'date' => '16th', 'month' => 'December'], $match);
    }

    /**
     * @throws \Exception
     */
    public function testExtractDate()
    {
        $now = new \DateTimeImmutable('2018-06-01');

        $date = AbstractHtmlScraper::extractDate('The date will be Fri 4th Oct', $now);
        $this->assertInstanceOf(\DateTimeInterface::class, $date);
        $this->assertEquals('2018-10-04', $date->format('Y-m-d'));

        $date = AbstractHtmlScraper::extractDate('Something happens on Sunday Jan 18, just FYI', $now);
        $this->assertInstanceOf(\DateTimeInterface::class, $date);
        $this->assertEquals('2019-01-18', $date->format('Y-m-d'));
    }


}
