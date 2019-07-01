<?php

namespace Caramel\FCGI;

class FCGIRequestContext
{
  public function __construct($remoteIP, $protocol)
  {
    $this->remoteIP = $remoteIP;
    $this->protocol = $protocol;
  }
}
