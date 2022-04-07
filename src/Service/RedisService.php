<?php

namespace App\Service;

use App\Service\PostService;
use Redis;
// use Symfony\Component\Cache\Adapter\RedisAdapter;


class RedisService {

    /** @var Redis $redis */
    private $redis;
    private $port;
    private $host;
    private PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->redis = new Redis();
        $this->host = '127.0.0.1';
        $this->port = 6379;
        $this->redis->connect($this->host, $this->port);

        $this->postService = $postService;
    }

    public function getLastPosts($numberOfPosts, $ttl = 60)
    {
        $this->connect();

        if (empty($posts = json_decode($this->get('last_posts'), true))) {
            $posts = $this->postService->getLastPosts($numberOfPosts);
            $this->set('last_posts', json_encode($posts), $ttl);
        }
        
        return $posts;
    }

    public function set($key, $value, int $ttl = 60) 
    {
        $this->connect();

        $this->redis->setex($key, $ttl, $value);
        return $this;
    }

    public function get($key)
    {
        $this->connect();

        return $this->redis->get($key);
    }

    private function connect()
    {
        if ($this->redis->ping() !== 'PONG') {
            $this->redis->connect($this->host, $this->port);
        }
    }
}