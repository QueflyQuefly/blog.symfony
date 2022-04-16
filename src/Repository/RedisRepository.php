<?php

namespace App\Repository;

use Redis;

class RedisRepository {

    /** @var Redis $redis */
    private $redis;

    private $port;

    private $host;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->host  = '127.0.0.1';
        $this->port  = 6379;
        $this
            ->redis
            ->connect($this->host, $this->port);
    }

    public function set(string $key, mixed $value, int $ttl = 60) 
    {
        $this
            ->redis
            ->setex($key, $ttl, $value);

        return $this;
    }

    public function get(string $key)
    {
        return $this
            ->redis
            ->get($key);
    }
}