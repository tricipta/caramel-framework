<?php

namespace Caramel\HTTPClient;

class AsyncHTTPClient
{
  public function __construct()
  {
    $this->initialize();
  }

  public function initialize($defaults=null)
  {
    $this->defaults = HTTPRequest::$_DEFAULTS;
    if ($defaults) {
      $this->defaults = array_merge($this->defaults, $defaults);
    }
    $this->closed = false;
  }

  public function close()
  {
    if ($this->closed) {
      return false;
    }
    $this->closed = true;
  }

  public function get($request, $headers=[], $auth=[], $callback=null)
  {
    $this->fetch($request, "GET", $headers, null, $auth, $callback);
  }

  public function post($request, $headers=[], $body=null, $auth=[], $callback=null)
  {
    $this->fetch($request, "POST", $headers, $body, $auth, $callback);
  }

  public function fetch($request, $method="GET", $headers=[], $body=null, $auth=[], $callback=null, $raiseError=true)
  {
    if ($this->closed) {
      throw new \Exception("fetch() called on closed AsyncHTTPClient");
    }
    if (!($request instanceof HTTPRequest)) {
      $request = new HTTPRequest($request, $method, $headers, $body, $auth);
    }
    $request = new RequestProxy($request, $this->defaults);
    $this->fetchImpl($request, $callback);
  }

  public function fetchImpl($request, $callback)
  {
    throw new \Exception("Not implemented error");
  }
}
