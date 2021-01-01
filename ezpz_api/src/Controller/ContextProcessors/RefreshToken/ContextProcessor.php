<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\RefreshToken;

use Ezpizee\ContextProcessor\Base as BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function requiredAccessToken(): bool {return false;}

  public function isSystemUser(): bool {return false;}

  public function methods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function exec(): void {
    die('/api/user/token/refresh/737CE9B5-B5CB-4F35-BB25-A35B02479A8C/1');
    $this->setContextData([]);
  }
}
