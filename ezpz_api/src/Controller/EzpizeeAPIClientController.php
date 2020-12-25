<?php

namespace Drupal\ezpz_api\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Site\Settings;
use Ezpizee\ConnectorUtils\Client;
use Drupal\ezpz_api\Controller\ContextProcessors\User\Profile\ContextProcessor as UserProfileCP;
use Ezpizee\MicroservicesClient\Config;
use Ezpizee\Utils\StringUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unirest\Request\Body;


class EzpizeeAPIClientController extends ControllerBase
{
  /**
   * @var ImmutableConfig
   */
  private $ezpzConfig;
  private $client;
  private $request;
  private $method;
  private $contentType;
  private $body;
  private $uri;

  public function __construct()
  {
    $this->ezpzConfig = Drupal::config('ezpz_portal.settings');
    if ($this->ezpzConfig->get('client_id') &&
      $this->ezpzConfig->get('client_secret') &&
      $this->ezpzConfig->get('app_name') &&
      $this->ezpzConfig->get('env')) {
      $env = $this->ezpzConfig->get('env');
      $microserviceConfig = new Config([
        'client_id' => $this->ezpzConfig->get('client_id'),
        'client_secret' => $this->ezpzConfig->get('client_secret'),
        'token_uri' => Client::getTokenUri(),
        'app_name' => $this->ezpzConfig->get('app_name'),
        'env' => $env
      ]);
      $this->request = Drupal::request();
      $this->client = new Client(Client::apiSchema($env), Client::apiHost($env), $microserviceConfig);
      $this->client->setPlatform('drupal');
      $this->client->setPlatformVersion(Drupal::VERSION);
      if ($env === 'local') {
        $this->client->verifyPeer(false);
      }
      // added user request
      $this->addHeaderRequest('user_id', Drupal::currentUser()->id());
      $this->uri = $this->request->query->get('endpoint');
      $this->method = $this->request->getMethod();
      $this->contentType = $this->request->headers->get('content-type');
      $this->body = empty($this->request->request->all()) ? json_decode($this->request->getContent(), true) : $this->request->request->all();
      if (empty($this->body))  {
        $this->body = [];
      }
    }
  }

  public function restApiClient(): JsonResponse
  {
    if (!empty($this->uri)) {
      if (StringUtil::startsWith($this->uri, "api/v1/drupal/") || StringUtil::startsWith($this->uri, "/api/v1/drupal/")) {
        return $this->requestToDrupal();
      }
      return $this->requestToMicroServices();
    }
    else {
      return new JsonResponse(
        ['status'=>'error','code'=>Response::HTTP_INTERNAL_SERVER_ERROR,'message'=>'Missing Ezpizee endpoint'],
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  private function requestToDrupal(): JsonResponse
  {
    $drupalApi = new EzpizeeAPIClientDrupalApiController($this->client);
    $res = $drupalApi->load($this->method, $this->uri);
    return new JsonResponse($res, $res['code']);
  }

  private function requestToMicroServices(): JsonResponse
  {
    if ($this->method === 'GET') {
      $response = $this->client->get($this->uri);
      $res = json_decode($response, true);
      if (isset($res['data']) && isset($res['data']['created_by'])) {
        $userProfileCP = new UserProfileCP($this->client);
        $userInfo = $userProfileCP->getUserInfoById((int)$res['data']['created_by']);
        $res['data']['created_by'] = $userInfo;
        $res['data']['modified_by'] = $userInfo;
      }
      return new JsonResponse($res, $res['code']);
    }
    else if ($this->method === 'POST') {
      if (isset($this->contentType) && $this->contentType === 'application/json') {
        $response = $this->client->post($this->uri, $this->body);
        $res = json_decode($response, true);
        return new JsonResponse($res, $res['code']);
      }
      else if (isset($this->contentType) && strpos($this->contentType, 'multipart/form-data;') !== false) {
        if ($this->hasFileUploaded()) {
          $response = $this->submitFormDataWithFile();
          $res = json_decode($response, true);
          return new JsonResponse($res, $res['code']);
        } else {
          $response = $this->submitFormData();
          $res = json_decode($response, true);
          return new JsonResponse($res, $res['code']);
        }
      }
      else {
        return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
      }
    }
    else if ($this->method === 'PUT') {
      if (isset($this->contentType) && $this->contentType === 'application/json') {
        $response = $this->client->put($this->uri, $this->body);
        $res = json_decode($response, true);
        return new JsonResponse($res, $res['code']);
      }
      else {
        return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
      }
    }
    else if ($this->method === 'DELETE') {
      $response = $this->client->delete($this->uri, $this->body);
      $res = json_decode($response, true);
      return new JsonResponse($res, $res['code']);
    }
    else if ($this->method === 'PATCH') {
      $response = $this->client->patch($this->uri, $this->body);
      $res = json_decode($response, true);
      return new JsonResponse($res, $res['code']);
    }
    else {
      return new JsonResponse(null, Response::HTTP_METHOD_NOT_ALLOWED);
    }
  }

  private function submitFormDataWithFile(): \Ezpizee\MicroservicesClient\Response
  {
    $fileUploaded = $this->uploadFile();
    $this->body[$fileUploaded['fileFieldName']] = Body::file($fileUploaded['filename'], $fileUploaded['mimetype'], $fileUploaded['postname']);
    $response = $this->client->postFormData($this->uri, $this->body);
    return $response;
  }

  private function submitFormData(): \Ezpizee\MicroservicesClient\Response
  {
    $response = $this->client->postFormData($this->uri, $this->body);
    return $response;
  }

  private function hasFileUploaded(): bool
  {
    return isset($_FILES) && !empty($_FILES);
  }

  private function addHeaderRequest(string $key, string $value): void
  {
    $this->client->addHeader($key, $value);
  }

  private function uploadFile(): array
  {
    $files = $this->request->files;
    $keys = $files->keys();
    $fileFieldName = $keys[0];
    if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName])) {
      throw new HttpException(400, 'File name not found');
    }
    if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName]) && $_FILES[$fileFieldName]['error'] > 0) {
      throw new HttpException(400, 'File could not be processed');
    }
    return [
      'fileFieldName' => $fileFieldName,
      'filename' => $_FILES[$fileFieldName]['tmp_name'],
      'mimetype' => $_FILES[$fileFieldName]['type'],
      'postname' => $_FILES[$fileFieldName]['name']
    ];
  }
}
