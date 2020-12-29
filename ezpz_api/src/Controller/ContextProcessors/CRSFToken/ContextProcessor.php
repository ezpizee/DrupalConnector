<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\CRSFToken;

use Drupal;
use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function methods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function exec(): void {
    $this->setContext(['token' => Drupal::csrfToken()->get()]);
  }
}
