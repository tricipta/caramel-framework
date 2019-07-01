<?php

namespace Caramel\Web;

class GzipContentEncoding extends OutputTransform
{
  public $CONTENT_TYPES = array('application/javascript', 'application/json');
  public $MIN_LENGTH = 5;

  public function __construct($request)
  {
    $this->gzipping = 'gzip';
  }

  public function transformFirstChunk($statusCode, $headers, $chunk, $finishing)
  {
    if ($this->gzipping) {

    }
    if ($this->gzipping) {

    }
    return array($statusCode, $headers, $chunk);
  }

  public function transformChunk($chunk, $finishing)
  {
    if ($this->gzipping) {

    }
    return $chunk;
  }
}
