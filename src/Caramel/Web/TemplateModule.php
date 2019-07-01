<?php

namespace Caramel\Web;

class TemplateModule extends UIModule
{
  public $name = 'Template';

  public function addFunctions()
  {
    return [
      'static_url' => [[$this, 'staticUrl']],
      'reverse_url' => [[$this, 'reverseUrl']],
      'full_url' => [[$this, 'fullUrl']],
      'xsrf_form_html' => [[$this, 'xsrfFromHtml']]
    ];
  }

  public function staticUrl($path, $theme=null)
  {
    return $this->handler->staticUrl($path, $theme, true);
  }

  public function reverseUrl()
  {
    return $this->handler->reverseUrl();
  }

  public function fullUrl()
  {
    return $this->handler->fullUrl();
  }

  public function xsrfFormHtml()
  {
    return $this->handler->xsrfFormHtml();
  }
}
