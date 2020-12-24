<?php

namespace Drupal\ezpz_api\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Ezpizee\MicroservicesClient\Client;
use Ezpizee\MicroservicesClient\Config;
use Ezpizee\Utils\StringUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unirest\Request\Body;


class EzpizeeAPIClientController extends ControllerBase
{
  private $client;
  private $request;
  private $method;
  private $contentType;
  private $body;
  private $uri;

  public function __construct()
  {
    $microserviceSchema = '';
    $microserviceHost = '';
    $microserviceConfig = new Config([
      'client_id' => '',
      'client_secret' => '',
      'token_uri' => ''
    ]);
    $this->request = Drupal::request();
    $this->client = new Client($microserviceSchema, $microserviceHost, $microserviceConfig);
    // added user request
    $this->addHeaderRequest('user_id', \Drupal::currentUser()->id());
    $this->uri = $this->request->query->get(Settings::get('api_microservice_querystring_key'));
    $this->method = $this->request->getMethod();
    $this->contentType = $this->request->headers->get('content-type');
    $this->body = empty($this->request->request->all()) ? json_decode($this->request->getContent(), true) : $this->request->request->all();
    if (empty($this->body))  $this->body = [];
    if (!isset($this->uri)) return new JsonResponse(['message' => 'Invalid uri'], Response::HTTP_INTERNAL_SERVER_ERROR);
  }

  public function restApiClient(): JsonResponse
  {
    if (StringUtil::startsWith($this->uri, "api/v1/drupal/")) {
      return $this->requestToDrupal();
    }
    return $this->requestToMicroServices();
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
        $userProfileCP = new \Drupal\ezpz_api\Controller\ContextProcessors\User\Profile\ContextProcessor($this->client);
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
      } else if (isset($this->contentType) && strpos($this->contentType, 'multipart/form-data;') !== false) {
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
    } else if ($this->method === 'PUT') {
      if (isset($this->contentType) && $this->contentType === 'application/json') {
        $response = $this->client->put($this->uri, $this->body);
        $res = json_decode($response, true);
        return new JsonResponse($res, $res['code']);
      }
    } else if ($this->method === 'DELETE') {
      $response = $this->client->delete($this->uri, $this->body);
      $res = json_decode($response, true);
      return new JsonResponse($res, $res['code']);
    } else if ($this->method === 'PATCH') {
      $response = $this->client->patch($this->uri, $this->body);
      $res = json_decode($response, true);
      return new JsonResponse($res, $res['code']);
    } else {
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
