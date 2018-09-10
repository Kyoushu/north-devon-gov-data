<?php

namespace Tests\Kyoushu\NorthDevonGovData\Model;

use Kyoushu\NorthDevonGovData\Model\BinCollection;
use Tests\Kyoushu\NorthDevonGovData\TestCase;

class BinCollectionTest extends TestCase
{

    public function testGetHumanType()
    {
        $collection = new BinCollection(BinCollection::TYPE_BLACK_BIN, 'foo', new \DateTimeImmutable('2018-01-01'));
        $this->assertEquals('Black Bin', $collection->getHumanType());

        $collection = new BinCollection(BinCollection::TYPE_GREEN_BIN, 'foo', new \DateTimeImmutable('2018-01-01'));
        $this->assertEquals('Green Bin', $collection->getHumanType());

        $collection = new BinCollection(BinCollection::TYPE_RECYCLING, 'foo', new \DateTimeImmutable('2018-01-01'));
        $this->assertEquals('Recycling', $collection->getHumanType());

        $collection = new BinCollection('foo', 'foo', new \DateTimeImmutable('2018-01-01'));
        $this->assertNull($collection->getHumanType());
    }

}