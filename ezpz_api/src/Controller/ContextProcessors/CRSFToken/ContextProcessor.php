<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\CRSFToken;

use Drupal;
use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  public function requiredAccessToken(): bool {return false;}

  public function allowedMethods(): array {return ['GET'];}

  public function validRequiredParams(): bool {return true;}

  public function processContext(): void {
    $this->setContext(['token' => Drupal::csrfToken()->get()]);
  }
}
