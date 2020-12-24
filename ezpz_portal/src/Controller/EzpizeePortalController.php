<?php

namespace Drupal\ezpz_portal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ezpz_api\Controller\ContextProcessors\User\Profile\ContextProcessor;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Unirest\Request;

class EzpizeePortalController extends ControllerBase
{
  public function portalSPA()
  {
    $this->checkAuthenticated();
    // check if there's installed data -> get https://local-cdn.ezpz.solutions/adminui/0.0.1/index.drupal.html#/
    // otherwise -> https://local-cdn.ezpz.solutions/install/html/index.drupal.html#/
    Request::verifyPeer(false);
    $resp = Request::get('https://local-cdn.ezpz.solutions/install/html/index.drupal.html');
    $html = $resp->raw_body;
    $this->formatSPAOutput($html);
    return new Response(
      $html,
      Response::HTTP_OK,
      array('content-type' => 'text/html; charset=UTF-8')
    );
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

  private function formatSPAOutput(&$spaHTMLContent): void
  {
    $userProfileCP = new ContextProcessor();
    $userProfileCP->exec();
    $baseUrl = \Drupal::request()->getSchemeAndHttpHost();
    $patterns = [
      '{baseURL}',
      '"{HHD_HEALTH_USER}"',
      '{logout}',
      "\n", "\r", "\t", "\s+"
    ];
    $replaces = [
      \Drupal::request()->getSchemeAndHttpHost(),
      json_encode($userProfileCP->getContextData()),
      $baseUrl . '/user/logout',
      "", "", ""
    ];
    $override = str_replace($patterns, $replaces, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'hhd_health_override.js'));
    $spaHTMLContent = str_replace('<' . 'head>', '<' . 'head' . '><' . 'script>' . $override . '</' . 'script>', $spaHTMLContent);
  }
}
