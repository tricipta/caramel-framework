<?php

namespace Caramel\Web;

class RequestDispatcher
{
  public function __construct($application, $connection)
  {
    $this->application = $application;
    $this->connection = $connection;
    $this->request = null;
    $this->chunks = [];
    $this->handlerClass = null;
    $this->handlerKArgs = null;
    $this->pathArgs = [];
    $this->pathKArgs = [];
  }

  public function setRequest($request)
  {
    $this->request = $request;
    $this->findHandler();
  }

  protected function findHandler()
  {
    $app = $this->application;
    $handlers = $app->getHostHandlers($this->request);
    if (!$handlers) {
      $this->handlerClass = 'Caramel\Web\RedirectHandler';
      $this->handlerKArgs = ['url' => 'http://' . $app->defaultHost . '/'];
      return false;
    }
    foreach($handlers as $spec) {
      if (preg_match($spec->regex, $this->request->path, $matches)) {
        $this->handlerClass = $spec->handlerClass;
        $this->handlerKArgs = $spec->kargs;
        if ($spec->regexGroup) {
          array_shift($matches);
          foreach ($matches as $key => $s) {
            if (is_int($key)) {
              $this->pathArgs[] = $s;
            } else {
              $this->pathKArgs[$key] = $s;
            }
          }
        }
        return false;
      }
    }
    if (array_key_exists('default_handler_class', $app->settings)) {
      $this->handlerClass = $app->settings['default_handler_class'];
      $this->handlerKArgs = array_key_exists('default_handler_args', $app->settings) ? $app->settings['default_handler_args'] : [];
    } else {
      $this->handlerClass = 'Caramel\Web\ErrorHandler';
      $this->handlerKArgs = ['status_code' => 404];
    }
  }

  public function execute()
  {
    $this->handler = new $this->handlerClass($this->application, $this->request, $this->handlerKArgs);
    $transforms = [];
    foreach ($this->application->transforms as $t) {
      $transforms[] = $t($this->request);
    }
    $this->handler->execute($transforms, $this->pathArgs, $this->pathKArgs);

    return $this->handler->preparedFuture;
  }
}
