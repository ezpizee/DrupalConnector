<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\ExpireIn;

use Ezpizee\ContextProcessor\Base as BaseContextProcessor;
use Ezpizee\MicroservicesClient\Client;

class ContextProcessor extends BaseContextProcessor
{
  public function requiredAccessToken(): bool {return false;}

  public function isSystemUser(): bool {return false;}

  public function methods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function exec(): void {
    $expireIn = $this->microserviceClient->getExpireIn($this->microserviceClient->getConfig(Client::KEY_ACCESS_TOKEN));
    $this->setContextData(['expire_in', $expireIn]);
  }
}
