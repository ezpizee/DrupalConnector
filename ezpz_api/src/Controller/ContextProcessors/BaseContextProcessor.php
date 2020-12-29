<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors;

use Drupal;
use Ezpizee\MicroservicesClient\Client;
use Ezpizee\Utils\RequestEndpointValidator;

abstract class BaseContextProcessor
{
  protected $context = [
    'message' => 'success',
    'code' => 200,
    'data' => null
  ];

  /**
   * @var Client
   */
  protected $microserviceClient;
  protected $requestData;

  public function __construct(Client $client=null) {
    $this->microserviceClient = $client;
    $this->requestData = empty(Drupal::request()->request->all()) ? json_decode(Drupal::request()->getContent(), true) : Drupal::request()->request->all();
    if (empty($this->requestData))  $this->requestData = [];
  }

  abstract public function methods(): array;

  abstract public function exec(): void;

  abstract public function validRequiredParams(): bool;

  protected final function getUriParam($key): string {return RequestEndpointValidator::getUriParam($key);}

  public final function setContext(array $context) {$this->context = $context;}
  public final function setContextData(array $data) {$this->context['data']=$data;}
  public final function setContextCode(int $code) {$this->context['code']=$code;}
  public final function setContextMessage(string $msg) {$this->context['message']=$msg;}

  public final function getContext(): array {return $this->context;}

  public final function getContextCode(): int {return $this->context['code'];}
  public final function getContextMessage(): string {return $this->context['message'];}
  public final function getContextData(): array {return empty($this->context['data']) ? [] : $this->context['data'];}
}
