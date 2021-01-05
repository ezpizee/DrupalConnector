<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\ExpireIn;

use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;
use Ezpizee\MicroservicesClient\Client;

class ContextProcessor extends BaseContextProcessor
{
  public function processContext()
  : void
  {
    $tokenKey = $this->microserviceClient->getConfig(Client::KEY_ACCESS_TOKEN);
    $token = $this->microserviceClient->getToken($tokenKey);
    $this->setContextData(['expire_in', $token->getExpireIn()]);
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
