<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\AuthenticatedUser;

use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function processContext()
  : void
  {
    $res = $this->microserviceClient->get('/api/user/me');
    $this->setContextData($res->get('data'));
  }

  protected function requiredAccessToken()
  : bool
  {
    return false;
  }

  protected function allowedMethods()
  : array
  {
    return ['GET'];
  }

  protected function validRequiredParams()
  : bool
  {
    return true;
  }
}
