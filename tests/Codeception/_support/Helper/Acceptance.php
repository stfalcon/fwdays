<?php
namespace Helper;

use Codeception\Util\Uri;

class Acceptance extends \Codeception\Module
{
    private $webDriver;

    public function seeCurrentHostEquals($uri) {
        $this->webDriver = $this->getModule('WebDriver');
        $url = $this->webDriver->webDriver->getCurrentURL();
        $host = Uri::retrieveHost($url);

        $this->assertEquals($uri, $host);
    }
}
