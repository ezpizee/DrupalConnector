<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\AuthenticatedUser;

use Ezpizee\ContextProcessor\Base as BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function requiredAccessToken(): bool {return false;}

  public function isSystemUser(): bool {return false;}

  public function methods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function exec(): void {
    $res = $this->microserviceClient->get('/api/user/me');
    $this->setContextData($res->get('data'));
  }
}
