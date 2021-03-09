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
    '/api/drupal/refresh/token'      => 'Drupal\ezpz_api\Controller\ContextProcessors\RefreshToken',
    '/api/drupal/expire-in'          => 'Drupal\ezpz_api\Controller\ContextProcessors\ExpireIn',
    '/api/drupal/authenticated-user' => 'Drupal\ezpz_api\Controller\ContextProcessors\AuthenticatedUser',
    '/api/drupal/crsf-token'         => 'Drupal\ezpz_api\Controller\ContextProcessors\CRSFToken'
  ];

  public function __construct(Client $client)
  {
    $this->checkAuthenticated();
    $this->microserviceClient = $client;
  }

  private function checkAuthenticated()
  {
    if (!$this->currentUser()->isAuthenticated()) {
      $baseUrl = Drupal::request()->getSchemeAndHttpHost();
      $requestUri = Drupal::request()->getRequestUri();
      $response = new RedirectResponse($baseUrl . '/user/login?destination=' . $requestUri, 302);
      $response->send();
      return;
    }
  }

  public function load(string $uri)
  : array
  {
    $uri = str_replace('//', '/', '/' . $uri);
    RequestEndpointValidator::validate($uri, $this->endpoints);
    $namespace = RequestEndpointValidator::getContextProcessorNamespace();
    $class = new $namespace();
    if ($class instanceof BaseContextProcessor) {
      $class->setMicroServiceClient($this->microserviceClient);
      $requestData = empty(Drupal::request()->request->all())
        ? json_decode(Drupal::request()->getContent(), true)
        : Drupal::request()->request->all();
      $class->setRequestData(empty($requestData) ? [] : $requestData);
      return $class->getContext();
    }
    return ['code' => 404, 'message' => 'Invalid namespace: ' . $namespace, 'data' => null];
  }
}
