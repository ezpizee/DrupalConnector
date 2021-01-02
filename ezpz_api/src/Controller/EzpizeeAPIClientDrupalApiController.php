<?php

namespace Drupal\ezpz_api\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;
use Ezpizee\MicroservicesClient\Client;
use Ezpizee\Utils\RequestEndpointValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;


class EzpizeeAPIClientDrupalApiController extends ControllerBase
{
  /**
   * @var Client
   */
  private $microserviceClient;
  private $endpoints = [
    '/api/v1/drupal/refresh/token' => 'Drupal\ezpz_api\Controller\ContextProcessors\RefreshToken',
    '/api/v1/drupal/expire-in' => 'Drupal\ezpz_api\Controller\ContextProcessors\ExpireIn',
    '/api/v1/drupal/authenticated-user' => 'Drupal\ezpz_api\Controller\ContextProcessors\AuthenticatedUser',
    '/api/v1/drupal/crsf-token' => 'Drupal\ezpz_api\Controller\ContextProcessors\CRSFToken'
  ];

  public function __construct(Client $client)
  {
    $this->checkAuthenticated();
    $this->microserviceClient = $client;
  }

  public function load(string $method, string $uri): array
  {
    $uri = str_replace('//', '/', '/'.$uri);
    RequestEndpointValidator::validate($uri, $this->endpoints);
    $namespace = RequestEndpointValidator::getContextProcessorNamespace();
    $class = new $namespace();
    if ($class instanceof BaseContextProcessor) {

      $class->setMicroServiceClient($this->microserviceClient);
      $requestData = empty(Drupal::request()->request->all())
        ? json_decode(Drupal::request()->getContent(), true)
        : Drupal::request()->request->all();
      $class->setRequestData(empty($requestData)?[]:$requestData);

      if (!in_array($method, $class->allowedMethods())) {
        $class->setContextCode(405);
        $class->setContextMessage('Method not allowed');
        return $class->getContext();
      } else if (!$class->validRequiredParams()) {
        $class->setContextMessage('INVALID_VALUE_FOR_REQUIRED_FIELD');
        $class->setContextCode(422);
        return $class->getContext();
      } else {
        $class->processContext();
        return $class->getContext();
      }
    }
    return ['code'=>404, 'message'=>'Invalid namespace: '.$namespace, 'data'=>null];
  }

  private function checkAuthenticated()
  {
    if (!$this->currentUser()->isAuthenticated()) {
      $baseUrl = \Drupal::request()->getSchemeAndHttpHost();
      $requestUri = \Drupal::request()->getRequestUri();
      $response = new RedirectResponse($baseUrl . '/user/login?destination=' . $requestUri, 302);
      $response->send();
      return;
    }
  }
}
