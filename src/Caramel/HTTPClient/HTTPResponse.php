<?php

namespace Caramel\HTTPClient;

class HTTPResponse
{
  public function __construct($code, $headers=null, $buffer=null, $error=null, $reason=null)
  {
    $this->code = $code;
    $this->reason = $reason;
    if ($headers) {
      $this->headers = $headers;
    }
    $this->buffer = $buffer;
    $this->body = null;
    if (!$error) {
      if ($this->code < 200 || $this->code >= 300) {
        $this->error = new HTTPError($this->reason);
      } else {
        $this->error = null;
      }
    } else {
      $this->error = $error;
    }
  }

  public function body()
  {
    if (!$this->buffer) {
      return null;
    } else if (!$this->body) {
      $this->body = $this->buffer;
    }
    return $this->body;
  }
}
