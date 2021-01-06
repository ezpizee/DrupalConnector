<?php

namespace Drupal\ezpz_api\Controller;

include_once explode(DIRECTORY_SEPARATOR.'ezpz'.DIRECTORY_SEPARATOR, __DIR__)[0].DIRECTORY_SEPARATOR.'ezpz'.
  DIRECTORY_SEPARATOR.'ezpzlib'.DIRECTORY_SEPARATOR.'autoload.php';

use Drupal;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\ezpz_api\Controller\ContextProcessors\User\Profile\ContextProcessor as UserProfileCP;
use Ezpizee\ConnectorUtils\Client;
use Ezpizee\MicroservicesClient\Config;
use Ezpizee\Utils\ResponseCodes;
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
    if ($this->ezpzConfig->get(Client::KEY_CLIENT_ID) &&
      $this->ezpzConfig->get(Client::KEY_CLIENT_SECRET) &&
      $this->ezpzConfig->get(Client::KEY_APP_NAME) &&
      $this->ezpzConfig->get(Client::KEY_ENV)) {
      $env = $this->ezpzConfig->get(Client::KEY_ENV);
      $microserviceConfig = new Config([
        Client::KEY_CLIENT_ID     => $this->ezpzConfig->get(Client::KEY_CLIENT_ID),
        Client::KEY_CLIENT_SECRET => $this->ezpzConfig->get(Client::KEY_CLIENT_SECRET),
        Client::KEY_TOKEN_URI     => Client::getTokenUri(),
        Client::KEY_APP_NAME      => $this->ezpzConfig->get(Client::KEY_APP_NAME),
        Client::KEY_ENV           => $env,
        Client::KEY_ACCESS_TOKEN  => Client::DEFAULT_ACCESS_TOKEN_KEY
      ]);
      $this->request = Drupal::request();
      $host = Client::apiHost($env);
      $tokenHandler = 'Drupal\ezpz_api\Controller\ContextProcessors\TokenHandler';
      $this->client = new Client(Client::apiSchema($env), $host, $microserviceConfig, $tokenHandler);
      $this->client->setPlatform('drupal');
      $this->client->setPlatformVersion(Drupal::VERSION);
      $this->addHeaderRequest('user_id', Drupal::currentUser()->id());
      if ($env === 'local') {
        Client::setIgnorePeerValidation(true);
      }
      $this->uri = $this->request->query->get('endpoint');
      if ($this->uri && $this->uri[0] !== '/') {
        $this->uri = str_replace('//', '/', '/' . $this->uri);
      }
      $this->method = $this->request->getMethod();
      $cType = $this->request->headers->get('content-type');
      $this->contentType = explode(';', empty($cType) ? '' : $cType)[0];
      $this->body = empty($this->request->request->all()) ? json_decode($this->request->getContent(), true) : $this->request->request->all();
      if (empty($this->body)) {
        $this->body = [];
      }
    }
  }

  private function addHeaderRequest(string $key, string $value)
  : void
  {
    $this->client->addHeader($key, $value);
  }

  public function restApiClient()
  : JsonResponse
  {
    if (!empty($this->uri)) {
      if (StringUtil::startsWith($this->uri, "/api/v1/drupal/")) {
        return $this->requestToDrupal();
      }
      return $this->requestToMicroServices();
    }
    else {
      return new JsonResponse(
        ['status' => 'error', 'code' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Missing Ezpizee endpoint'],
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  private function requestToDrupal()
  : JsonResponse
  {
    $drupalApi = new EzpizeeAPIClientDrupalApiController($this->client);
    $res = $drupalApi->load($this->uri);
    return new JsonResponse($res, isset($res['code']) ? $res['code'] : 200);
  }

  private function requestToMicroServices()
  : JsonResponse
  {
    $res = [
      'status'  => 'ERROR',
      'code'    => ResponseCodes::CODE_METHOD_NOT_ALLOWED,
      'data'    => null,
      'message' => ResponseCodes::MESSAGE_ERROR_INVALID_METHOD
    ];
    if ($this->method === 'GET') {
      $response = $this->client->get($this->uri);
      $res = json_decode($response, true);
      if (isset($res['data']) && isset($res['data']['created_by'])) {
        $userProfileCP = new UserProfileCP();
        $userProfileCP->setMicroServiceClient($this->client);
        $requestData = empty(Drupal::request()->request->all())
          ? json_decode(Drupal::request()->getContent(), true)
          : Drupal::request()->request->all();
        $userProfileCP->setRequestData(empty($requestData) ? [] : $requestData);

        $userInfo = $userProfileCP->getUserInfoById((int)$res['data']['created_by']);
        $res['data']['created_by'] = $userInfo;
        $res['data']['modified_by'] = $userInfo;
      }
    }
    else if ($this->method === 'POST') {
      $this->validateCSRFToken();
      if ('application/json' === $this->contentType || strpos($this->contentType, 'application/json') !== false) {
        $response = $this->client->post($this->uri, $this->body);
        $res = json_decode($response, true);
      }
      else if ('multipart/form-data' === $this->contentType || strpos($this->contentType, 'multipart/form-data') !== false) {
        if ($this->hasFileUploaded()) {
          $response = $this->submitFormDataWithFile();
          $res = json_decode($response, true);
        }
        else {
          $response = $this->submitFormData();
          $res = json_decode($response, true);
        }
      }
      else {
        $res['message'] = 'INVALID_CONTENT_TYPE';
      }
    }
    else if ($this->method === 'PUT') {
      $this->validateCSRFToken();
      if ('application/json' === $this->contentType || strpos($this->contentType, 'application/json') !== false) {
        $response = $this->client->put($this->uri, $this->body);
        $res = json_decode($response, true);
      }
      else {
        $res['message'] = 'INVALID_CONTENT_TYPE';
      }
    }
    else if ($this->method === 'DELETE') {
      $this->validateCSRFToken();
      $response = $this->client->delete($this->uri, $this->body);
      $res = json_decode($response, true);
    }
    else if ($this->method === 'PATCH') {
      $this->validateCSRFToken();
      $response = $this->client->patch($this->uri, $this->body);
      $res = json_decode($response, true);
    }

    return new JsonResponse($res, isset($res['code']) ? $res['code'] : 200);
  }

  private function validateCSRFToken()
  {
    if (Drupal::csrfToken()->get() !== $this->request->headers->get('CSRF-Token')) {
      return new JsonResponse(['message' => 'Invalid CSRF Token', 'code' => 422, 'status' => 'Error'], 422);
    }
  }

  private function hasFileUploaded()
  : bool
  {
    return isset($_FILES) && !empty($_FILES);
  }

  private function submitFormDataWithFile()
  : \Ezpizee\MicroservicesClient\Response
  {
    $fileUploaded = $this->uploadFile();
    $this->body[$fileUploaded['fileFieldName']] = Body::file($fileUploaded['filename'], $fileUploaded['mimetype'], $fileUploaded['postname']);
    $response = $this->client->postFormData($this->uri, $this->body);
    return $response;
  }

  private function uploadFile()
  : array
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
      'filename'      => $_FILES[$fileFieldName]['tmp_name'],
      'mimetype'      => $_FILES[$fileFieldName]['type'],
      'postname'      => $_FILES[$fileFieldName]['name']
    ];
  }

  private function submitFormData()
  : \Ezpizee\MicroservicesClient\Response
  {
    $response = $this->client->postFormData($this->uri, $this->body);
    return $response;
  }
}
