<?php

namespace Drupal\ezpz_api\Controller\ContextProcessors;

use Drupal;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\TempStoreException;
use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;
use Ezpizee\Utils\Logger;

class TokenHandler implements TokenHandlerInterface
{
    private $key = '';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function keepToken(Token $token): void {
        if ($this->key) {
            $tempstore = Drupal::getContainer()->get('tempstore.private');
            if ($tempstore instanceof PrivateTempStoreFactory) {
                $tempstore = $tempstore->get('ezpz_api');
                if ($tempstore instanceof PrivateTempStore) {
                    try {
                        $tempstore->set($this->key, serialize($token));
                    }
                    catch (TempStoreException $e) {
                        Logger::error($e->getMessage(), 500);
                    }
                }
            }
        }
    }

    public function getToken(): Token {
        if ($this->key) {
            $tempstore = Drupal::getContainer()->get('tempstore.private');
            if ($tempstore instanceof PrivateTempStoreFactory) {
                $tempstore = $tempstore->get('ezpz_api');
                if ($tempstore instanceof PrivateTempStore) {
                    $token = unserialize($tempstore->get($this->key));
                    if ($token instanceof Token) {
                        return $token;
                    }
                }
            }
        }
        return new Token([]);
    }
}
