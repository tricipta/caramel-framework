<?php

namespace Caramel\Payment;

use Caramel\HTTPClient;

class Gateway
{
  public function __construct($apiKey, $kargs=[])
  {
    $this->apiKey = $apiKey;
  }

  public function getHTTPClient()
  {
    return new HTTPClient\CurlHTTPClient();
  }
}
