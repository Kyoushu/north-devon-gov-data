<?php

namespace Tests\Kyoushu\NorthDevonGovData;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\RequestInterface;

class TestCase extends BaseTestCase
{

    /**
     * @param string $uri
     * @return callable
     */
    protected function matchRequestURI(string $uri)
    {
        return $this->callback(function(RequestInterface $request) use ($uri){
            return $request->getUri()->__toString() === $uri;
        });
    }

}