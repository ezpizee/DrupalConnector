<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors\User\Profile;

use Drupal;
use Drupal\ezpz_api\Controller\ContextProcessors\BaseContextProcessor;
use Drupal\user\Entity\User;

class ContextProcessor extends BaseContextProcessor
{
  public function processContext()
  : void
  {
    $this->setContextData($this->getUserInfoById((int)Drupal::currentUser()->id()));
  }

  public function getUserInfoById(int $id)
  {
    $user = User::load($id);
    if (!empty($this->microserviceClient) && !empty($user)) {
      $res = $this->microserviceClient->get('/api/user/me');
      $data = $res->get('data', []);
      return [
        "uid"         => $user->id(),
        "displayName" => $user->getDisplayName(),
        "profile"     => [
          'profile_picture' => '',
          'first_name'      => $user->get('field_first_name')->value,
          'last_name'       => $user->get('field_last_name')->value,
          'email'           => $user->getEmail(),
          'phone'           => $user->get('field_phone')->value
        ],
        "role"        => $user->getRoles(),
        "partnerInfo" => isset($data['partnerInfo']) ? $data['partnerInfo'] : []
      ];
    } else if (!empty($user)) {
      return [
        "uid"         => $user->id(),
        "displayName" => $user->getDisplayName(),
        "profile"     => [
          'profile_picture' => '',
          'first_name'      => $user->get('field_first_name')->value,
          'last_name'       => $user->get('field_last_name')->value,
          'email'           => $user->getEmail(),
          'phone'           => $user->get('field_phone')->value
        ],
        "role"        => $user->getRoles(),
        "partnerInfo" => []
      ];
    }
    return [];
  }

  protected function requiredAccessToken()
  : bool
  {
    return false;
  }

  protected function allowedMethods()
  : array
  {
    return ['GET'];
  }

  protected function validRequiredParams()
  : bool
  {
    return true;
  }
}
