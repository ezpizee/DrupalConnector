<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\ExpireIn;

use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function methods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function exec(): void {
    $this->setContextData([]);
  }
}
