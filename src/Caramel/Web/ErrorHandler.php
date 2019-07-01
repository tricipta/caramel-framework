<?php

namespace Caramel\Web;

class ErrorHandler extends RequestHandler
{
  public function initialize($args)
  {
    $statusCode = $args['status_code'];
    $this->setStatus($statusCode);
  }

  public function prepare()
  {
    throw new HTTPError($this->statusCode);
  }
}
