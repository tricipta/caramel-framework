<?php

namespace Caramel\Web;

class StaticFileHandler extends RequestHandler
{
  public function initialize($kargs)
  {
    $this->root = $kargs['path'];
    $this->defaultFilename = array_key_exists('default_filename', $kargs) ? $kargs['default_filename'] : null;
  }

  public function head($path)
  {
    return $this->get($path);
  }

  public function get($args)
  {
    $this->path = $args[0];
    unset($path);
    $absolutePath = $this->getAbsolutePath($this->root, $this->path);
    $this->absolutePath = $this->validateAbsolutePath($this->root, $absolutePath);
    if (!$this->absolutePath) {
      return false;
    }
    $this->setHeaders();
    $content = $this->getContent($this->absolutePath);
    $this->write($content);
  }

  public function getAbsolutePath($root, $path)
  {
    return $root . DS . $path;
  }

  public function validateAbsolutePath($root, $absolutePath)
  {
    if (substr_compare($absolutePath, $root, 1, strlen($root)) === 0) {
      throw new HTTPError(403, $this->path . ' is not in root static directory');
    }
    if (!file_exists($absolutePath)) {
      throw new HTTPError(404);
    }
    if (!is_file($absolutePath)) {
      throw new HTTPError(403, $this->path . ' is not a file');
    }
    return $absolutePath;
  }

  public function setHeaders()
  {
    $contentType = $this->getContentType();
    if ($contentType) {
      $this->setHeader('Content-Type', $contentType);
    }
  }

  public function getContent($absPath)
  {
    return file_get_contents($absPath);
  }

  public function getContentType()
  {
    $extension = pathinfo($this->absolutePath, PATHINFO_EXTENSION);

    if ($extension == 'css') {
      $mimeType = 'text/css';
    } else if ($extension == 'js') {
      $mimeType = 'text/javascript';
    } else if ($extension == 'woff') {
      $mimeType = 'application/x-font-woff';
    } else {
      if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->absolutePath);
        finfo_close($finfo);
      } else {
        $mimeType = 'application/octet-stream';
      }
    }
    return $mimeType;
  }

  public static function makeStaticUrl($settings, $theme=null, $path, $includeVersion=true)
  {
    if ($theme) {
      $url = array_key_exists('theme_url_prefix', $settings) ? $settings['theme_url_prefix'] . $theme . '/static/' . $path : '/themes/' . $theme . '/static/' . $path;
    } else {
      $url = array_key_exists('static_url_prefix', $settings) ? $settings['static_url_prefix'] . $path : '/static/' . $path;
    }
    if (!$includeVersion) {
      return $url;
    }
    $versionHash = $this->getVersion($settings, $path);
    if (!$versionHash) {
      return $url;
    }
    return $url . '?v=' . $versionHash;
  }

  public function getVersion($settings, $path)
  {
    $absPath = $this->getAbsolutePath($settings['static_path'], $path);
    return $this->getCachedVersion($absPath);
  }

  public function getCachedVersion($absPath)
  {
    return '123';
  }
}
