<?php

namespace Caramel\Web;

class HTTPError extends \Exception
{
  public $responses = [
    200 => 'OK',
    301 => 'Moved Permanently',
    302 => 'Found',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout'
  ];

  public function __construct($statusCode, $logMessage=null, $kargs=[])
  {
    $this->statusCode = $statusCode;
    $this->logMessage = $logMessage;
    $this->reason = array_key_exists('reason', $kargs) ? $kargs['reason'] : null;
  }

  public function __toString()
  {
    if (!$this->reason) {
      $this->reason = array_key_exists($this->statusCode, $this->responses) ? $this->responses[$this->statusCode] : 'Unknown';
    }
    $message = 'HTTP ' . $this->statusCode . ' ' . $this->reason;
    if ($this->logMessage) {
      return $message . ' (' . $this->logMessage . ') ';
    } else {
      return $message;
    }
  }
}
