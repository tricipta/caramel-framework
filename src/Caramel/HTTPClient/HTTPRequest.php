<?php

namespace Caramel\HTTPClient;

class HTTPRequest
{
  public static $_DEFAULTS = [
    "connectTimeout" => 20,
    "requestTimeout" => 20,
    "followRedirects" => true,
    "maxRedirects" => 5,
  ];

  public function __construct($url, $method="GET", $headers=[], $body=null, $userAgent=null, $auth=[])
  {
    $this->url = $url;
    $this->method = $method;
    $this->headers = $headers;
    $this->body = $body;
    $this->userAgent = $userAgent;
    $this->$auth = $auth;
  }
}
