<?php

namespace Caramel\Web;

class UIModule extends \Twig\Extension
{
  public function __construct($handler)
  {
    $this->handler = $handler;
    $this->request = $handler->request;
    $this->application = $handler->application;
    $this->ui = $handler->ui;
    $this->locale = $handler->locale();
  }

  public function currentUser()
  {
    return $this->handler->currentUser();
  }

  public function getName()
  {
    return $this->name;
  }

  public function addTokenParsers()
  {
    return [];
  }

  public function addFilters()
  {
    return [];
  }

  public function addFunctions()
  {
    return [];
  }

  public function getTokenParsers()
  {
    $tokenParsers = [];

    return $tokenParsers;
  }

  public function getFilters()
  {
    $filters = [];
    if (empty($this->functions)) {
      $this->filters = $this->addFilters();
    } else {
      $this->filters = array_merge($this->filters, $this->addFilters());
    }

    foreach ($this->filters as $key => $filter) {
      if (!isset($filter[1])) {
        $filter[1] = [];
      }
      $filters[] = new \Twig\SimpleFilter($key, $filter[0], $filter[1]);
    }

    return $filters;
  }

  public function getFunctions()
  {
    $functions = [];
    if (empty($this->functions)) {
      $this->functions = $this->addFunctions();
    } else {
      $this->functions = array_merge($this->functions, $this->addFunctions());
    }

    foreach ($this->functions as $key => $function) {
      if (!isset($function[1])) {
        $function[1] = [];
      }
      $functions[] = new \Twig\SimpleFunction($key, $function[0], $function[1]);
    }

    return $functions;
  }

  public function getGlobals()
  {
    $globals = [];

    return $globals;
  }
}
