<?php

namespace Caramel\HTTPClient;

class RequestProxy
{
  public function __construct($request, $defaults)
  {
    $this->request = $request;
    $this->defaults = $defaults;
  }

  public function __get($name) {
    $requestAttr = $this->request->$name;
    if ($requestAttr !== null) {
      return $requestAttr;
    } else if ($this->defaults && array_key_exists($name, $this->defaults)) {
      return $this->defaults[$name];
    } else {
      return null;
    }
  }
}
