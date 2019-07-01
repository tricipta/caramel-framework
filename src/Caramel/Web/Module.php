<?php

namespace Caramel\Web;

class Module
{
  public function __construct($application, $kargs=[])
  {
    $this->application = $application;
    $this->settings = $application->settings;

    $this->initialize($kargs);
  }
}
