<?php

namespace Caramel\FCGI;

use Caramel\HTTPUtil;

class FCGIAdapter
{
  public function __construct($application)
  {
    $this->application = $application;
  }

  public function run($environ, $startResponse)
  {
    $method = $environ['REQUEST_METHOD'];
    $uri = (!empty($environ['QUERY_STRING'])) ? $environ['QUERY_STRING'] : 'url=/';
    $headers = [];
    if (array_key_exists('CONTENT_TYPE', $environ)) {
      $headers['Content-Type'] = $environ['CONTENT_TYPE'];
    }
    if (array_key_exists('CONTENT_LENGTH', $environ)) {
      $headers['Content-Length'] = $environ['CONTENT_LENGTH'];
    }
    foreach ($environ as $key => $value) {
      if (substr($key, 0, 5) == 'HTTP_') {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $environ[$key];
      }
    }
    if (array_key_exists('Content-Length', $headers)) {
      $body = file_get_contents('php://input');
    } else {
      $body = '';
    }
    $protocol = $environ['REQUEST_SCHEME'];
    $remoteIP = $environ['REMOTE_ADDR'];
    if (array_key_exists('HTTP_HOST', $environ)) {
      $host = $environ['HTTP_HOST'];
    } else {
      $host = $environ['SERVER_NAME'];
    }
    $files = $_FILES;
    $connection = new FCGIConnection($method, $startResponse, new FCGIRequestContext($remoteIP, $protocol));
    $request = new HTTPUtil\HTTPServerRequest($method, $uri, 'HTTP/1.1', $headers, $body, $host, $files, $connection);
    $request->parseBody();
    $this->application->startRequest($request);
    if ($connection->error) {
      throw $connection->error;
    }
    if (!$connection->finished) {
      throw new Exception('request did not finish synchronously');
    }
    return $connection->writeBuffer;
  }
}
