<?php

namespace Semaphore;

class Semaphore
{
    /**@var $memcache \Memcached*/
    private $memcache;

    public function __construct($memcache)
    {
        $this->memcache = $memcache;
    }

    public function acquire(string $key, int $seconds): bool
    {
        return $this->memcache->add($this->getLockKey($key), 1, $seconds);
    }

    public function release(string $key): bool
    {
        return $this->memcache->delete($this->getLockKey($key));
    }

    private function getLockKey(string $key)
    {
        return crc32('LOCK' . $key);
    }

}