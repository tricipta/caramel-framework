<?php

namespace Caramel\FCGI;

use Caramel\HTTPUtil;

class FCGIConnection extends HTTPUtil\HTTPConnection
{
  public function __construct($method, $startResponse, $context)
  {
    $this->method = $method;
    $this->startResponse = $startResponse;
    $this->context = $context;
    $this->writeBuffer = [];
    $this->finished = false;
    $this->expectedContentRemaining = null;
    $this->error = null;
  }

  public function setCloseCallback($callback)
  {

  }

  public function writeHeader($startLine, $headers, $chunk=null, $callback=null)
  {
    if ($this->method == 'HEAD') {
      $this->expectedContentRemaining = 0;
    } else if (array_key_exists('Content-Length', $headers)) {
      $this->expectedContentRemaining = $headers['Content-Length'];
    } else {
      $this->expectedContentRemaining = null;
    }
    call_user_func_array($this->startResponse, [$startLine['code'] . ' ' . $startLine['reason'], $headers]);
    if ($chunk) {
      $this->write($chunk, $callback);
    } else if ($callback != null) {
      call_user_func_array($callback, []);
    }
    return null;
  }

  public function write($chunk, $callback=null)
  {
    if ($this->expectedContentRemaining) {
      $this->expectedContentRemaining -= strlen($chunk);
      if ($this->expectedContentRemaining < 0) {
        $this->error = new HTTPUtil\HTTPOutputError("Tried to write more data than Content-Length");
        throw $this->error;
      }
    }
    $this->writeBuffer[] = $chunk;
    if ($callback) {
      call_user_func_array($callback, []);
    }
    return null;
  }

  public function finish()
  {
    if ($this->expectedContentRemaining && $this->expectedContentRemaining != 0) {
      $this->error = new HTTPUtil\HTTPOutputError("Tried to write " . $this->expectedContentRemaining . " bytes less than Content-Length");
      throw $this->error;
    }
    $this->finished = true;
  }
}
