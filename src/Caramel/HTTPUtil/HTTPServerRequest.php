<?php

namespace Caramel\HTTPUtil;

class HTTPServerRequest
{
  public function __construct($method=null, $uri=null, $version='HTTP/1.0', $headers=[], $body='', $host=null, $files=[], $connection=null)
  {
    $this->method = $method;
    $this->uri = $uri;
    $this->version = $version;
    $this->headers = !empty($headers) ? $headers : [];
    $this->body = !empty($body) ? $body : '';

    $context = $connection->context;
    $this->remoteIP = $context->remoteIP;
    $this->protocol = $context->protocol;

    $this->host = $host;
    $this->hostName = "";
    $this->files = !empty($files) ? $files : [];
    $this->connection = $connection;
    $this->startTime = time();
    $this->finishTime = null;

    parse_str($uri, $url);
    $this->path = $url['url'];
    unset($url['url']);
    $this->query = null;
    if (!empty($url)) {
      $this->query = http_build_query($url);
    }
    parse_str($this->query, $this->arguments);
    $this->queryArguments = $this->arguments;
    $this->bodyArguments = [];
  }

  public function supportHTTP11() {
    return $this->version = 'HTTP/1.1';
  }

  public function cookies()
  {
    if (!isset($this->cookies)) {
      $this->cookies = [];
      if (array_key_exists('Cookie', $this->headers)) {
        $cookies = explode(';', $this->headers['Cookie']);
        foreach ($cookies as $cookie) {
          parse_str($cookie, $morsel);
          foreach($morsel as $name => $value) {
            $this->cookies[$name] = $value;
          }
        }
      }
    }
    return $this->cookies;
  }

  public function write($chunk, $callback=null)
  {
    $this->connection->write($chunk, $callback);
  }

  public function finish()
  {
    $this->connection->finish();
    $this->finishTime = time();
  }

  public function requestTime()
  {
    if ($this->finishTime) {
      return time() - $this->startTime;
    } else {
      return $this->finishTime - $this->startTime;
    }
  }

  public function fullUrl()
  {
    return $this->protocol . '://' . $this->host . $this->uri;
  }

  public function parseBody()
  {
    parseBodyArguments(array_key_exists('Content-Type', $this->headers) ? $this->headers['Content-Type'] : '', $this->body, $this->bodyArguments, $this->files, $this->headers);
    foreach($this->bodyArguments as $k => $v) {
      $this->arguments[$k] = $v;
    }
  }
}
