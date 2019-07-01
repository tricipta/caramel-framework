<?php

namespace Caramel\Web;

class OutputTransform
{
  public function __construct($request)
  {

  }

  public function transformFirstChunk($statusCode, $headers, $chunk, $finishing)
  {
    return [$statusCode, $headers, $chunk];
  }

  public function transformChunk($chunk, $finishing)
  {
    return $chunk;
  }
}
