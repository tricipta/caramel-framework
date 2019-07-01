<?php

namespace Caramel\Web;

use Caramel\HTTPUtil;
use Caramel\Template;

class RequestHandler
{
  protected $SUPPORTED_METHOD = ['GET', 'HEAD', 'POST', 'DELETE', 'PUT', 'OPTIONS'];
  protected $_ARG_DEFAULT = [];

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

  public function __construct($application, $request, $kargs=[])
  {
    $this->application = $application;
    $this->request = $request;
    $this->headersWritten = false;
    $this->finished = false;
    $this->autoFinish = true;
    $this->transforms = null;
    $this->headers = null;
    $this->preparedFuture = null;
    $this->pathArgs = null;
    $this->pathKArgs = [];
    $this->ui = [];
    $this->ui['modules'] = $application->uiModules;
    $this->templates = $application->templates;
    $this->theme = 'default';

    $this->clear();
    $this->request->connection->setCloseCallback([$this, 'onConnectionClose']);
    $this->initialize($kargs);
  }

  public function settings()
  {
    return $this->application->settings;
  }

  public function initialize()
  {

  }

  public function get($args=[], $kargs=[])
  {
    throw new HTTPError(405);
  }

  public function head($args=[], $kargs=[])
  {
    throw new HTTPError(405);
  }

  public function post($args=[], $kargs=[])
  {
    throw new HTTPError(405);
  }

  public function delete($args=[], $kargs=[])
  {
    throw new HTTPError(405);
  }

  public function put($args=[], $kargs=[])
  {
    throw new HTTPError(405);
  }

  public function options($args=[], $kargs=[])
  {
    throw new HTTPError(405);
  }

  public function prepare()
  {

  }

  public function onFinish()
  {

  }

  public function onConnectionClose()
  {

  }

  public function clear()
  {
    $this->headers = [
      'Server' => 'Caramel/1.0',
      'Content-Type' => 'text/html; charset=UTF-8',
      'Date' => null
    ];
    $this->setDefaultHeaders();
    $this->writeBuffer = [];
    $this->statusCode = 200;
    $this->reason = $this->responses[200];
  }

  public function setDefaultHeaders()
  {

  }

  public function setStatus($statusCode, $reason=null)
  {
    $this->statusCode = $statusCode;
    if ($reason) {
      $this->reason = $reason;
    } else {
      if (array_key_exists($statusCode, $this->responses)) {
        $this->reason = $this->responses[$statusCode];
      } else {
        throw new \Exception('unknown status code ' . $statusCode);
      }
    }
  }

  public function getStatus()
  {
    return $this->statusCode;
  }

  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }

  public function addHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }

  public function clearHeader($name)
  {
    if (array_key_exists($name, $this->headers)) {
      unset($this->headers[$name]);
    }
  }

  public function getArgument($name, $default=null, $strip=true)
  {
    return $this->_getArgument($name, $default, $this->request->arguments, $strip);
  }

  public function getArguments($name, $strip=true)
  {
    return $this->_getArguments($name, $this->request->arguments, $strip);
  }

  public function getBodyArgument($name, $default=null, $strip=true)
  {
    return $this->_getArgument($name, $default, $this->request->bodyArguments, $strip);
  }

  public function getBodyArguments($name, $strip=true)
  {
    return $this->_getArguments($name, $this->request->bodyArguments, $strip);
  }

  public function getQueryArgument($name, $default=null, $strip=true)
  {
    return $this->_getArgument($name, $default, $this->request->queryArguments, $strip);
  }

  public function getQueryArguments($name, $strip=true)
  {
    return $this->_getArguments($name, $this->request->queryArguments, $strip);
  }

  protected function _getArgument($name, $default, $source, $strip=true)
  {
    $args = $this->_getArguments($name, $source, $strip);
    if (!$args) {
      if ($default === []) {
        throw new \Exception($name);
      }
      return $default;
    }
    return $args[0];
  }

  protected function _getArguments($name, $source, $strip=true)
  {
    $values = [];
    if (array_key_exists($name, $source)) {
      $v = $source[$name];
      $v = $this->decodeArgument($v, $name);
      if ($strip && !is_array($v)) {
        $v = trim($v);
      }
      $values[] = $v;
    }
    return $values;
  }

  public function decodeArgument($value, $name=null)
  {
    return $value;
  }

  public function cookies()
  {
    return $this->request->cookies();
  }

  public function getCookie($name, $default=null)
  {
    if ($this->request->cookies() && array_key_exists($name, $this->request->cookies())) {
      $cookies = $this->request->cookies();
      return $cookies[$name];
    }
    return $default;
  }

  public function setCookie($name, $value, $domain=null, $expires=null, $path='/', $expiresDays=null, $kargs=[])
  {
    if (is_array($value)) {
      throw new \Exception('Invalid cookie ' . $name);
    }
    if (!isset($this->newCookie)) {
      $this->newCookie = [];
    }
    if (!array_key_exists($name, $this->newCookie)) {
      $this->newCookie[$name] = [];
    }
    $this->newCookie[$name]['value'] = $value;
    $morsel = $this->newCookie[$name];
    if ($domain) {
      $morsel['domain'] = $domain;
    } else {
      $morsel['domain'] = null;
    }
    if ($expiresDays && !$expires) {
      $expires = time() + ($expiresDays * 24 * 60 * 60);
    }
    if ($expires) {
      $morsel['expires'] = $expires;
    } else {
      $morsel['expires'] = null;
    }
    if ($path) {
      $morsel['path'] = $path;
    }
    $this->newCookie[$name] = $morsel;
  }

  public function clearCookie($name, $path='/', $domain=null)
  {
    $expires = time() + (365 * 24 * 60 * 60);
    $this->setCookie($name, '', $domain, $expires, $path);
  }

  public function clearAllCookies($path='/', $domain=null)
  {
    foreach ($this->request->cookies() as $name => $value) {
      $this->clearCookie($name, $path, $domain);
    }
  }

  public function setSecureCookie($name, $value, $expiresDays=30, $version=null, $kargs=[])
  {
    $this->requireSetting('cookie_secret', 'Cookie Secret');
    $this->setCookie($name, $this->createSignedValue($name, $value, $version), null, null, '/', $expiresDays, $kargs);
  }

  public function createSignedValue($name, $value, $version=null)
  {
    $this->application->settings['cookie_secret'];
    return base64_encode($value);
  }

  public function getSecureCookie($name, $value=null, $minVersion=null)
  {
    $this->requireSetting('cookie_secret', 'Cookie Secret');
    if (!$value) {
      $value = $this->getCookie($name);
    }
    return $this->decodeSignedValue($this->application->settings['cookie_secret'], $name, $value, $minVersion);
  }

  public function decodeSignedValue($secret, $name, $value, $minVersion=null)
  {
    return base64_decode($value);
  }

  public function redirect($url, $permanent=false, $status=null)
  {
    if ($this->headersWritten) {
      throw new \Exception('Cannot redirect after headers have been written');
    }
    if (!$status) {
      $status = $permanent ? 301 : 302;
    } else {

    }
    $this->setStatus($status);
    $this->setHeader('Location', utf8_encode($url));
    $this->finish();
  }

  public function write($chunk)
  {
    if ($this->finished) {
      throw new \Exception('Cannot write() after finish()');
    }

    if (is_array($chunk)) {
      $chunk = json_encode($chunk);
      $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
    }
    $chunk = utf8_encode($chunk);
    $this->writeBuffer[] = $chunk;
  }

  public function render($templateName, $kargs=[])
  {
    if ($this->finished) {
      throw new \Exception('Cannot render() after finish()');
    }
    $html = $this->renderString($templateName, $kargs);
    $this->finish($html);
  }

  public function renderString($templateName, $kargs=[])
  {
    $templatePath = $this->getTemplatePath();
    if (!$templatePath) {
      $templatePath = '';
    }
    $themePath = $this->getThemePath();
    if (!$themePath) {
      $themePath = '';
    }
    $loader = $this->createTemplateLoader($templatePath, $themePath);
    $t = $loader->loadTemplate($templateName);
    $namespace = $this->getTemplateNamespace();
    $namespace = array_merge($kargs, $namespace);
    return $t->render($namespace);
  }

  public function getTemplatePath()
  {
    return array_key_exists('template_path', $this->application->settings) ? $this->application->settings['template_path'] : null;
  }

  public function getThemePath()
  {
    return array_key_exists('theme_path', $this->application->settings) ? $this->application->settings['theme_path'] : null;
  }

  public function createTemplateLoader($templatePath, $themePath=null)
  {
    $settings = $this->application->settings;
    if (array_key_exists('template_loader', $settings)) {
      return $settings['template_loader'];
    }
    $args = [];
    $fileLoader = new \Twig\Loader\FilesystemLoader($templatePath);
    if ($themePath) {
      if (isset($this->theme)) {
        $fileLoader->prependPath($themePath . DS . $this->theme . DS . 'templates' . DS);
      }
    }
    $templates = $this->application->templates;
    if ($templates) {
      foreach ($templates as $name => $templatePath) {
        $fileLoader->addPath($templatePath, $name);
      }
    }
    $loaders = [$fileLoader];
    $loader = new \Twig\Loader\ChainLoader($loaders);
    $options = [];
    if (array_key_exists('template_options', $settings)) {
      $options = $settings['template_options'];
    }
    $twig = new \Twig\Environment($loader, $options);
    foreach ($this->ui['modules'] as $uiModule) {
      $twig->addExtension(new $uiModule($this));
    }
    return $twig;
  }

  public function getTemplateNamespace()
  {
    $namespace = [
      'handler' => $this,
      'request' => $this->request,
      'current_user' => $this->currentUser(),
      'Locale' => $this->locale()
    ];
    $namespace = array_merge($namespace, $this->ui);
    return $namespace;
  }

  public function flush($includeFooters=false, $callback=null)
  {
    $chunk = join('', $this->writeBuffer);
    $this->writeBuffer = [];
    if (!$this->headersWritten) {
      $this->headersWritten = true;
      foreach($this->transforms as $transform) {
        /* TODO */
      }
      if ($this->request->method == 'HEAD') {
        $chunk = null;
      }
      if (isset($this->newCookie)) {
        foreach($this->newCookie as $name => $cookie) {
          setcookie($name, $cookie['value'], $cookie['expires'], $cookie['path'], $cookie['domain']);
        }
      }
      $startLine = [
        'version' => $this->request->version,
        'code' => $this->statusCode,
        'reason' => $this->reason
      ];
      return $this->request->connection->writeHeader($startLine, $this->headers, $chunk, $callback);
    } else {
      foreach($this->transforms as $transform) {
        /* TODO */
      }
      if ($this->request->method != 'HEAD') {
        return $this->request->connection->write($chunk, $callback);
      } else {
        return null;
      }
    }
  }

  public function finish($chunk=null) {
    if ($this->finished) {
      throw new \Exception('finish() called twice.');
    }

    if ($chunk) {
      $this->write($chunk);
    }

    if (!$this->headersWritten) {
      if ($this->statusCode == 200 && in_array($this->request->method, ['GET', 'HEAD']) && !array_key_exists('Etag', $this->headers)) {
        $this->setEtagHeader();
        if ($this->checkEtagHeader()) {
          $this->writeBuffer = [];
          $this->setStatus(304);
        }
      }
      if ($this->statusCode == 304) {
        $this->clearHeaderFor304();
      } else if (!array_key_exists('Content-Length', $this->headers)) {
        $contentLength = 0;
        foreach ($this->writeBuffer as $part) {
          $contentLength += strlen($part);
        }
        $this->setHeader('Content-Length', $contentLength);
      }
    }
    if ($this->request->connection) {
      $this->request->connection->setCloseCallback(null);
    }
    $this->flush(true);
    $this->request->finish();
    $this->finished = true;
    $this->onFinish();
    $this->ui = null;
  }

  public function sendError($statusCode=500, $kargs=[])
  {
    if ($this->headersWritten) {
      if (!$this->finished) {
        $this->finish();
      }
      return false;
    }

    $this->clear();
    $reason = $kargs["reason"];
    if (array_key_exists('exc_info', $kargs)) {
      $exception = $kargs['exc_info'];
      if ($exception instanceof HTTPError && $exception->reason) {
        $reason = $exception->reason;
      }
    }
    $this->setStatus($statusCode, $reason);
    $this->writeError($statusCode, $kargs);
    if (!$this->finished) {
      $this->finish();
    }
  }

  public function writeError($statusCode, $kargs=[])
  {
    if (array_key_exists('exc_info', $kargs) && array_key_exists('serve_traceback', $this->settings())) {
      $this->setHeader('Content-Type', 'text/plain');
      $this->write($kargs['exc_info']->getTraceAsString());
      $this->finish();
    } else {
      $this->finish('<html><title>' . $statusCode . ': ' . $this->reason . '</title>'
      .'<body>' . $statusCode . ': ' . $this->reason . '</body></html>');
    }
  }

  public function locale()
  {
    if (!isset($this->locale)) {
      $this->locale = $this->getUserLocale();
      if (!$this->locale) {
        $this->locale = $this->getBrowserLocale();
      }
    }
    return $this->locale;
  }

  public function getUserLocale()
  {
    return null;
  }

  public function getBrowserLocale($default="en_US")
  {
    if (array_key_exists('Accept-Language', $this->request->headers)) {
      $languages = explode(',', $this->request->headers['Accept-Language']);
      $locales = [];
      foreach ($languages as $language) {
        $parts = explode(';', trim($language));
        if (count($parts) > 1 && substr_compare($parts[1], 'q=', 1, 2) !== 0) {
          $score = substr($parts[1], 0, -2);
        } else {
          $score = 1.0;
        }
        $locales[] = [$parts[0], $score];
      }
      if ($locales) {
        /* TODO */
      }
    }
    return $default;
  }

  public function currentUser()
  {
    if (!isset($this->currentUser)) {
      $this->currentUser = $this->getCurrentUser();
    }
    return $this->currentUser;
  }

  public function setCurrentUser($value)
  {
    $this->currentUser = $value;
  }

  public function getCurrentUser()
  {
    return null;
  }

  public function getLoginUrl()
  {
    $this->requireSetting('login_url', '/login');
    return $this->application->settings['login_url'];
  }

  public function xsrfToken()
  {
    if (!isset($this->xsrfToken)) {
      /* TODO */
    }
    return $this->xsrfToken;
  }

  public function checkXsrfCookie()
  {
    $token = $this->getArgument('_xsrf', null);
    if (!$token) {
      throw new HTTPError(403, '"_xsrf" argument missing from POST');
    }
    /* TODO */
  }

  public function xsrfFormHtml()
  {
    return '<input type="hidden" name="_xsrf" value="' . $this->xsrfToken() . '">';
  }

  public function staticUrl($path, $theme=null, $includeHost=false, $kargs=null)
  {
    $this->requireSetting('static_path', 'static_url');
    $staticHandlerClass = array_key_exists('static_handler_class', $this->settings()) ? $this->settings()['static_handler_class'] : '\Caramel\Web\StaticFileHandler';
    $getUrl = [$staticHandlerClass, 'makeStaticUrl'];
    $base = '';
    if ($includeHost) {
      $base = $this->request->protocol . '://' . $this->request->host;
    }
    return $base . call_user_func_array([$this, 'makeStaticUrl'], [$this->settings(), $theme, $path, false]);
  }

  public function reverseUrl($name, $args=[])
  {
    return $this->application->reverseUrl($name, $args);
  }

  public function fullUrl($path, $includeHost=true, $args=[]) {
    $base = '';
    if ($includeHost) {
      $base = $this->request->protocol . '://' . $this->request->host;
    }
    if ($path[0] != '/') {
      $path = '/' . $path;
    }
    return $base . $path;
  }

  public function requireSetting($name, $feature='this feature')
  {
    if (!array_key_exists($name, $this->application->settings)) {
      throw new \Exception('You must define the ' . $name . ' setting in your application to use ' . $feature);
    }
  }

  public function computeEtag()
  {
    /* TODO */
  }

  public function setEtagHeader()
  {
    /* TODO */
  }

  public function checkEtagHeader()
  {
    /* TODO */
  }

  public function execute($transforms, $args, $kargs=[])
  {
    $this->transforms = $transforms;
    try {
      if (!in_array($this->request->method, $this->SUPPORTED_METHOD)) {
        throw new HTTPError(405);
      }

      foreach ($args as $arg) {
        $this->pathArgs[] = $this->decodeArgument($arg);
      }
      foreach ($kargs as $name => $v) {
        $this->pathKArgs[$name] = $this->decodeArgument($v, $name);
      }

      if (!in_array($this->request->method, ['GET', 'HEAD', 'OPTIONS']) && array_key_exists('xsrf_cookie', $this->application->settings)) {
        $this->checkXsrfCookie();
      }
      $result = $this->prepare();

      if ($this->finished) {
        return false;
      }

      $method = strtolower($this->request->method);
      $result = call_user_func_array([$this, $method], [$this->pathArgs, $this->pathKArgs]);

      if ($this->autoFinish && !$this->finished) {
        $this->finish();
      }
    } catch (\Exception $e) {
      logging('(Error) Request: <' . $this->request->uri . '> ' . $e->getMessage());
      $this->handleRequestException($e);
    }
  }

  protected function handleRequestException($e)
  {
    if ($this->finished) {
      return false;
    }
    if ($e instanceof HTTPError) {
      if (!array_key_exists($e->statusCode, $this->responses) && !$e->reason) {
        $this->sendError(500, ['exc_info' => $e]);
      } else {
        $this->sendError($e->statusCode, ['exc_info' => $e]);
      }
    } else {
      $this->sendError(500, ['exc_info' => $e]);
    }
  }

  public function clearHeaderFor304()
  {
    $headers = [
      "Allow", "Content-Encoding", "Content-Language",
      "Content-Length", "Content-MD5", "Content-Range",
      "Content-Type", "Last-Modified"
    ];
    foreach($headers as $h) {
      $this->clear_header($h);
    }
  }
}
