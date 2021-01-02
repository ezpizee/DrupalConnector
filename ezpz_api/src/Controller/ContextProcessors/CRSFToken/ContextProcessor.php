<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\CRSFToken;

use Drupal;
use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  protected function requiredAccessToken(): bool {return false;}

  protected function allowedMethods(): array {return ['GET'];}

  protected function validRequiredParams(): bool {return true;}

  public function processContext(): void {
    $this->setContext(['token' => Drupal::csrfToken()->get()]);
  }
}
