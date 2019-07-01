<?php

namespace Caramel\FCGI;

class FCGIServer
{
  public function __construct($adapter)
  {
    $this->adapter = $adapter;
  }

  function startResponse($status, $responseHeaders)
  {
    header('HTTP/1.1 ' . $status);
    $headers = array('Status' => $status);
    $headers = array_merge($responseHeaders, $headers);
    foreach($headers as $key => $header) {
      header($key . ': ' . $header);
    }
  }

  public function handleRequest()
  {
    $responses = $this->adapter->run($_SERVER, [$this, 'startResponse']);

    ob_start();
    foreach ($responses as $response) {
      echo $response;
    }
    $output = ob_get_contents();
    ob_end_clean();

    echo $output;
  }
}
