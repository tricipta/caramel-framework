<?php

use Caramel\Web;

function logging($message, $type=3, $options=[])
{
  $destination = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'caramel_log';

  if (!empty($options['destination'])) {
    $destination = $options['destination'];
  }
  if (is_array($message) || is_object($message)) {
    $message = json_encode($message);
  }
  $message = date('Y-m-d H:i:s') . " " . $message . "\r\n";

  error_log($message, $type, $destination);
}

function authenticated($handler) {
  if (!$handler->currentUser()) {
    if (in_array($handler->request->method, ['GET', 'HEAD'])) {
      $url = $handler->getLoginUrl();
      if (!strpos($url, '?')) {
        $parseUrl = parse_url($url);
        if (array_key_exists('scheme', $parseUrl)) {
          $nextUrl = $handler->request->fullUrl();
        } else {
          $nextUrl = $handler->request->uri;
        }
        $url .= '?' . http_build_query(['next' => $nextUrl]);
      }
      $handler->redirect($url);
      exit;
    }
    throw new Web\HTTPError(403);
  }
  return true;
}

function parseBodyArguments($contentType, $body, &$arguments, $files, $headers=null)
{
  if (strstr($contentType, 'application/x-www-form-urlencoded') !== false) {
    parse_str($body, $uriArguments);
    foreach ($uriArguments as $name => $value) {
      $arguments[$name] = $value;
    }
  }
  if (strstr($contentType, 'multipart/form-data') !== false) {
    $uriArguments = $_POST;
    foreach ($uriArguments as $name => $value) {
      $arguments[$name] = $value;
    }
    $files = $_FILES;
  }
  if (strstr($contentType, 'application/json') !== false) {
    $uriArguments = json_decode($body, true);
    foreach ($uriArguments as $name => $value) {
      $arguments[$name] = $value;
    }
  }
}
