<?php

namespace Caramel\Payment;

class Midtrans extends Gateway
{
  public function chargeUrl($body, $callback)
  {
    $client = $this->getHTTPClient();
    $client->post();
  }
}
