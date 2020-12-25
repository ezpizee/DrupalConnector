<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\AuthenticatedUser;

use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function methods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function exec(): void {
    $res = $this->microserviceClient->get('/api/user/me');
    $this->setContextData($res->get('data'));
  }
}
