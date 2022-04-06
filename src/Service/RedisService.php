<?php

namespace App\Service;

use \Redis;

class RedisService { //implements CacheItemInterface {

    /** @var Redis $redis */
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect($this->host, $this->port);
    }

    public function set($value) 
    {
        $this->redis->doSave();
        return $this;
    }

    public function get($key)
    {

    }

    public function getKey($value)
    {

    }

    public function isHit($key)
    {

    }

    public function expiresAt(?\DateTimeInterface $expiration)
    {
        return $this;
    }

    public function expiresAfter(int $time)
    {
        return $this;
    }
}
$ff = new RedisService();