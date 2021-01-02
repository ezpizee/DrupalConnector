<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors;

use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;

class TokenHandler implements TokenHandlerInterface
{
    private $key = '';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function keepToken(Token $token): void {
        if ($this->key) {
            $tempstore = \Drupal::service('user.private_tempstore')->get('ezpz_api');
            $tempstore->set($this->key, serialize($token));
        }
    }

    public function getToken(): Token {
        if ($this->key) {
            $tempstore = \Drupal::service('user.private_tempstore')->get('ezpz_api');
            $token = unserialize($tempstore->get($this->key));
            if ($token instanceof Token) {
                return $token;
            }
        }
        return new Token([]);
    }
}
