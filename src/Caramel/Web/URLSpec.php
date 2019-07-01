<?php

namespace Caramel\Web;

class URLSpec
{
  public function __construct($pattern, $handler, $name=null, $kargs=[])
  {
    $this->regex = '/^' . str_replace('/', '\/', $pattern) . '$/';
    $this->regexGroup = strpos($this->regex, '(') ? true : false;
    $this->handlerClass = $handler;
    $this->name = $name;
    $this->kargs = $kargs;

    list($this->path, $this->groupCount) = $this->findGroups();
  }

  protected function findGroups()
  {
    $pattern = $this->regex;
    $pieces = [];
    foreach (explode('(', $pattern) as $fragment) {

    }
    return [join('', $pieces), null];
  }
}
