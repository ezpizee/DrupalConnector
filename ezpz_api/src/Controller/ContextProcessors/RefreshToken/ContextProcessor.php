<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\RefreshToken;

use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function requiredAccessToken(): bool {return false;}

  public function allowedMethods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function processContext(): void {
    $this->setContextData([]);
    die('/api/user/token/refresh/737CE9B5-B5CB-4F35-BB25-A35B02479A8C/1');
  }
}
