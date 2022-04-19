<?php

namespace App\Service;

use App\Repository\RedisRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class RedisCacheService {

    private RedisRepository $redisRepository;

    private SerializerInterface $serializer;

    public function __construct(
        RedisRepository     $redisRepository,
        SerializerInterface $serializer
    ) {
        $this->redisRepository = $redisRepository;
        $this->serializer      = $serializer;
    }

    public function get(string $key, int $ttl, string $type, callable $function)
    {
        $handler = function ($object) {
            return $object->getId();
        };
        $parameters = [
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH     => true,
            AbstractObjectNormalizer::MAX_DEPTH_HANDLER    => $handler,
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => $handler,
        ];
        $cachedValue = $this
            ->redisRepository
            ->get($key);

        if (! empty($cachedValue)) {
            $deserializableValue = $this
                ->serializer
                ->deserialize($cachedValue, $type, 'json', $parameters);

            return $deserializableValue;
        }

        $uncachedValue     = $function();
        $serializableValue = $this
            ->serializer
            ->serialize($uncachedValue, 'json', $parameters);
        $this
            ->redisRepository
            ->set($key, $serializableValue, $ttl);
        
        return $uncachedValue;
    }

    public function getWithoutSerializer(string $key, int $ttl, callable $function)
    {
        $cachedValue = $this
            ->redisRepository
            ->get($key);

        if (! empty($cachedValue)) {
 
            return $cachedValue;
        }

        $uncachedValue = $function();
        $this
            ->redisRepository
            ->set($key, $uncachedValue, $ttl);
        
        return $uncachedValue;
    }
}