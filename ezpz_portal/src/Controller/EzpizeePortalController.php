<?php

namespace Drupal\ezpz_portal\Controller;

use Drupal;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Ezpizee\ConnectorUtils\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EzpizeePortalController extends ControllerBase
{
  /**
   * @var ImmutableConfig
   */
  private $ezpzConfig;

  private $mode = 'install';

  /**
   * @return Response
   */
  public function portalSPA()
  {
    $this->checkAuthenticated();
    $this->ezpzConfig = Drupal::config('ezpz_portal.settings');
    if ($this->ezpzConfig->get('client_id') &&
      $this->ezpzConfig->get('client_secret') &&
      $this->ezpzConfig->get('app_name') &&
      $this->ezpzConfig->get('env')) {
      $env = $this->ezpzConfig->get('env');
      $cdnUrl = Client::cdnSchema($env).Client::cdnHost($env).Client::adminUri('drupal');
      $this->mode = 'admin';
      if ($env === 'local') {
        Client::setIgnorePeerValidation(true);
      }
      $html = Client::getContentAsString($cdnUrl);
      $this->formatSPAOutput($html);
      return new Response(
        $html,
        Response::HTTP_OK,
        array('content-type' => 'text/html; charset=UTF-8')
      );
    }
    else {
      $baseUrl = Drupal::request()->getSchemeAndHttpHost();
      $response = new RedirectResponse($baseUrl . '/admin/config/services/ezpz/portal', 302);
      $response->send();
      return $response;
    }
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

  private function formatSPAOutput(&$spaHTMLContent): void
  {
    $patterns = ["\n", "\r", "\t", "\s+"];
    $replaces = ["", "", "", " "];
    if (!empty($this->ezpzConfig)) {
      $data = $this->ezpzConfig->getRawData();
      foreach ($data as $key=>$val) {
        $patterns[] = '{'.$key.'}';
        $replaces[] = $val;
      }
    }
    if ($this->mode === 'admin') {
      $baseUrl = Drupal::request()->getSchemeAndHttpHost();
      $requestUri = Drupal::request()->getRequestUri();
      $patterns[] = '{loginPageRedirectUrl}';
      $replaces[] = $baseUrl . '/user/login?destination=' . $requestUri;
    }
    $dir = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
    $override = str_replace($patterns, $replaces, file_get_contents($dir . 'ezpz_'.$this->mode.'_override.js'));
    $spaHTMLContent = str_replace('<' . 'head>', '<' . 'head' . '><' . 'script>' . $override . '</' . 'script>', $spaHTMLContent);
  }
}
