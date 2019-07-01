<?php

namespace Caramel\Web;

use \Exception;

class Application
{
  public function __construct($handlers=null, $defaultHost='', $transforms=null, $settings=[])
  {
    if (!$transforms) {
      $this->transforms = [];
      if (array_key_exists('compress_response', $settings) || array_key_exists('gzip', $settings)) {
        $this->transforms[] = new GzipContentEncoding();
      }
    } else {
      $this->transforms = $transforms;
    }

    $this->defaultHost = $defaultHost;
    $this->settings = $settings;
    $this->uiModules = [
      'Debug' => '\Twig\Extension\Debug',
      'Text' => '\Twig\Extensions\Extension\Text',
      'Template' => '\Caramel\Web\TemplateModule'
    ];
    $this->addUIModules(array_key_exists('ui_modules', $this->settings) ? $this->settings['ui_modules'] : []);

    $this->modules = [];
    $this->templates = [];

    $this->handlers = [];
    $this->namedHandlers = [];

    if (array_key_exists('static_path', $this->settings)) {
      $path = $this->settings['static_path'];
      $staticUrlPrefix = array_key_exists('static_url_prefix', $this->settings) ? $this->settings['static_url_prefix'] : '/static/';
      $staticHandlerClass = array_key_exists('static_handler_class', $this->settings) ? $this->settings['static_handler_class'] : '\Caramel\Web\StaticFileHandler';
      $staticHandlerArgs = array_key_exists('static_handler_args', $this->settings) ? $this->settings['static_handler_args'] : [];
      $staticHandlerArgs['path'] = $path;
      foreach ([$staticUrlPrefix . '(.*)', '/(favicon\.ico)', '/(robots\.txt)'] as $pattern) {
        $handlers[] = [$pattern, $staticHandlerClass, null, $staticHandlerArgs];
      }
    }

    if ($handlers) {
      $this->addHandlers('.*', $handlers);
    }
  }

  public function addHandlers($hostPattern, $hostHandlers)
  {
    if ($hostPattern[0] != '^') $hostPattern = '/^' . $hostPattern;
    if ($hostPattern[strlen($hostPattern) - 1] != '$') $hostPattern = $hostPattern . '$/';
    $handlers = [];

    foreach ($hostHandlers as $spec) {
      if (in_array(count($spec), [2, 3, 4])) {
        $name = null;
        $kargs = [];
        if (count($spec) == 2) {
          list($pattern, $handler) = $spec;
        } else if (count($spec) == 3) {
          if (is_array($spec[2])) {
            list($pattern, $handler, $kargs) = $spec;
          } else {
            list($pattern, $handler, $name) = $spec;
          }
        } else if (count($spec) == 4) {
          list($pattern, $handler, $name, $kargs) = $spec;
        }
        $spec = new URLSpec($pattern, $handler, $name, $kargs);
        $handlers[] = $spec;

        if ($spec->name) {
          if (array_key_exists($spec->name, $this->namedHandlers)) {
            logging('Multiple handlers named ' . $spec->name . '; replacing previous value');
          }
          $this->namedHandlers[$spec->name] = $spec;
        }
      }
    }

    if (array_key_exists($hostPattern, $this->handlers)) {
      $this->handlers[$hostPattern] = array_merge($handlers, $this->handlers[$hostPattern]);
    } else {
      $this->handlers[$hostPattern] = $handlers;
    }
  }

  public function getHostHandlers($request)
  {
    list($host) = explode(':', strtolower($request->host));
    $matches = [];
    foreach ($this->handlers as $pattern => $handlers) {
      if (preg_match($pattern, $host)) {
        $matches = array_merge($matches, $handlers);
      }
    }
    arsort($matches);

    return $matches;
  }

  public function addTransform($transformClass)
  {
    $this->transforms[] = $transformClass;
  }

  public function addUIModules($uiModules)
  {
    foreach ($uiModules as $name => $uiModule) {
      $this->uiModules[$name] = $uiModule;
    }
  }

  public function addModules($modules)
  {
    foreach ($modules as $module) {
      if (in_array(count($module), [2, 3])) {
        $kargs = [];
        if (count($module) == 2) {
          list($name, $moduleClass) = $module;
        } else if (count($module) == 3) {
          list($name, $moduleClass, $kargs) = $module;
        }

        require_once MODULE . DS . $name . DS . $moduleClass . '.php';
        $this->modules[$name] = new $moduleClass($this, $kargs);
      }
    }
  }

  public function addTemplate($name, $templatePath)
  {
    $this->templates[$name] = $templatePath;
  }

  public function startRequest($request)
  {
    $dispatcher = new RequestDispatcher($this, null);
    $dispatcher->setRequest($request);

    return $dispatcher->execute();
  }

  public function reverseUrl($name, $args)
  {
    if (array_key_exists($name, $this->namedHandlers)) {
      return $this->namedHandlers[$name];
    }
    throw new Exception($name . ' not found in named urls.');
  }

  public function logRequest()
  {
    /* TODO */
  }
}
