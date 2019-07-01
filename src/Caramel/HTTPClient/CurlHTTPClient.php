<?php

namespace Caramel\HTTPClient;

class CurlHTTPClient extends AsyncHTTPClient
{
  public function initialize($maxClients=10, $defaults=null)
  {
    parent::initialize($defaults);
    $this->multi = curl_multi_init();
    $this->curls = [];
    for ($i = 0; $i < $maxClients; $i++) {
      $this->curls[] = $this->curlCreate();
    }
    $this->freeList = $this->curls;
  }

  public function close()
  {
    foreach ($this->curls as $curl) {
      curl_close($curl);
    }
    curl_multi_close($this->multi);
  }

  public function fetchImpl($request, $callback)
  {
    $this->requests[] = $request;
    $this->processQueue();
    $this->handleResult($callback);
  }

  private function processQueue()
  {
    while (true) {
      $started = 0;
      while ($this->freeList && $this->requests) {
        $started = $started + 1;
        $curl = array_pop($this->freeList);
        $request = array_pop($this->requests);
        $this->curlSetupRequest($curl, $request);
        curl_multi_add_handle($this->multi, $curl);
      }
      if (!$started) {
        break;
      }
    }
  }

  private function handleResult($callback)
  {
    $active = null;
    do {
      $status = curl_multi_exec($this->multi, $active);
    } while ($status === CURLM_CALL_MULTI_PERFORM || $active);
    $this->finishPendingRequest($callback);
  }

  private function finishPendingRequest($callback)
  {
    while(true) {
      $info = curl_multi_info_read($this->multi);
      if (!empty($info["handle"])) {
        if ($info["result"] == CURLE_OK) {
          $this->finish($callback, $info["handle"]);
        } else {
          $this->finish($callback, $info["handle"], $info["result"]);
        }
      } else {
        break;
      }
    }
    $this->processQueue();
  }

  private function finish($callback, $curl, $curlError=null)
  {
    $info = curl_getinfo($curl);
    if ($curlError) {
      $error = new HTTPError($curlError);
      $reason = curl_error($curl);
      $code = $info["http_code"];
      $buffer = null;
    } else {
      $error = null;
      $reason = null;
      $code = $info["http_code"];
      $buffer = curl_multi_getcontent($curl);
    }

    curl_multi_remove_handle($this->multi, $curl);
    $this->freeList[] = $curl;

    $response = new HTTPResponse($code, null, $buffer, $error, $reason);
    if ($callback) {
      call_user_func_array($callback, [$response]);
    } else {
      return $response;
    }
  }

  private function curlCreate()
  {
    $curl = curl_init();
    return $curl;
  }

  private function curlSetupRequest($curl, $request)
  {
    curl_setopt($curl, CURLOPT_URL, $request->url);
    $request->request->headers[] = "Expect: ";
    $request->request->headers[] = "Pragma: ";
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request->headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $request->followRedirects);
    curl_setopt($curl, CURLOPT_MAXREDIRS, $request->maxRedirects);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, (1000 * $request->connectTimeout));
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, (1000 * $request->requestTimeout));
    if ($request->userAgent) {
      curl_setopt($curl, CURLOPT_USERAGENT, $request->userAgent);
    } else {
      curl_setopt($curl, CURLOPT_USERAGENT, "Caramel/1.0 (compatible; curl)");
    }
    $curlOptions = ["GET" => CURLOPT_HTTPGET, "POST" => CURLOPT_POST, "PUT" => CURLOPT_UPLOAD, "HEAD" => CURLOPT_NOBODY];
    foreach($curlOptions as $o) {
      curl_setopt($curl, $o, false);
    }
    if (array_key_exists($request->method, $curlOptions)) {
      curl_setopt($curl, $curlOptions[$request->method], true);
    }
    $bodyExpected = array_key_exists($request->method, ["POST", "PATCH", "PUT"]);
    $bodyPresent = $request->body;
    if ($bodyExpected || $bodyPresent) {
      if ($request->method == "GET") {
        throw new \Exception("Body must be null");
      }
      if ($request->method == "POST") {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request->body);
      } else {

      }
    }
    if (isset($request->auth)) {
      $userpwd = $request->auth["username"] . ":" . $request->auth["password"];
      if (!isset($request->auth["mode"]) || $request->auth["mode"] == "basic") {
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      } else if ($request->auth["mode"] == "digest") {
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
      } else {
        throw new \Exception("Unsupported auth_mode " . $request->authMode);
      }
      curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
    }
  }
}
