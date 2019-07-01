<?php

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('CARAMEL') or define('CARAMEL', dirname(__FILE__));

require_once CARAMEL . DS . 'Caramel' . DS . 'Utils.php';

spl_autoload_register('caramel_autoload', true, true);

function caramel_autoload($className) {
  $className = (string) str_replace('\\', DS, $className);
  list($namespace) = explode(DS, $className);
  if ($namespace == 'Caramel') {
    $fileName = CARAMEL . DS . $className . '.php';
  }
  try {
    if (isset($fileName)) {
      if(!file_exists($fileName)) {
        throw new Exception('Oops... We could not find the required file ' . $fileName . ' :(', 500);
      }

      require $fileName;
    }
  } catch (Exception $e) {

  }
}
