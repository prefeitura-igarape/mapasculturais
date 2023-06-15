<?php
declare(strict_types=1);

namespace MapasCulturais;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * @property-read AdapterInterface $adapter
 * @package MapasCulturais
 */
class Cache {
    use Traits\MagicGetter;

    protected AdapterInterface $adapter;

    protected array $items = [];

    protected string $namespace = '';

    function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    private function parseKey(string $key): string {
        // caracteres reservados: {}()/\@:
        $key = str_replace (
            ['{', '}', '(', ')', '/', '\\', '@', ':'],
            ['<', '>', '[', ']', '|', '|',  '%', '#'], 
            $key);

        return $this->namespace . $key;
    }

    protected function getCacheItem(string $key): CacheItem {
        $key = $this->parseKey($key);

        if (!isset($this->items[$key])) {
            $this->items[$key] = $this->adapter->getItem($key);
        } 
        return $this->items[$key];
    }

    function save(string $key, $value, int $cache_ttl = null) {
        $item = $this->getCacheItem($key);
        $item->expiresAfter($cache_ttl);
        $item->set($value);

    }

    function contains(string $key):bool {
        $item = $this->getCacheItem($key);
        return $item->isHit();
    }

    function delete(string $key) {
        $key = $this->parseKey($key);
        $this->adapter->deleteItem($key);
        unset($this->items[$key]);
    }

    function flushAll() {
        $this->adapter->clear();
    }

    function setNamespace(string $namespace = null) {
        $this->namespace = $namespace ?: '';
    }
}