<?php

namespace Caramel\Web;

class RedirectHandler extends RequestHandler
{
  public function initialize($kargs)
  {
    $this->url = $kargs['url'];
    $this->permanent = array_key_exists('permanent', $kargs) ? $kargs['permanent'] : false;
  }

  public function get()
  {
    $this->redirect($this->url, $this->permanent);
  }
}
