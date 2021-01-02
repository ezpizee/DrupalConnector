<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\ExpireIn;

use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;
use Ezpizee\MicroservicesClient\Client;

class ContextProcessor extends BaseContextProcessor
{
  public function requiredAccessToken(): bool {return false;}

  public function allowedMethods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function processContext(): void {
    $expireIn = $this->microserviceClient->getExpireIn($this->microserviceClient->getConfig(Client::KEY_ACCESS_TOKEN));
    $this->setContextData(['expire_in', $expireIn]);
  }
}
