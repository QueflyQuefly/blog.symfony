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

    /**
     * This function get value from cache, but if it is empty, saves it
     */
    public function get(string $key, int $ttl, string $type, callable $function)
    {
        $parameters  = $this->getParametersForSerializer();
        $cachedValue = $this
            ->redisRepository
            ->get($key);

        if (! empty($cachedValue)) {
            $deserializableValue = $this
                ->serializer
                ->deserialize($cachedValue, $type, 'json', $parameters);

            return $deserializableValue;
        }

        $uncachedValue = $function();
        $this->set($key, $ttl, $uncachedValue);
        
        return $uncachedValue;
    }

    /**
     * This function set value to cache by key
     */
    public function set(string $key, int $ttl, mixed $uncachedValue)
    {
        $parameters        = $this->getParametersForSerializer();
        $serializableValue = $this
            ->serializer
            ->serialize($uncachedValue, 'json', $parameters);
        $this
            ->redisRepository
            ->set($key, $serializableValue, $ttl);
    }

    /**
     * Returns parameters for Serializer with handler
     */
    private function getParametersForSerializer(): array
    {
        $handler = function ($object) {
            return $object->getId();
        };

        $parameters = [
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH     => true,
            AbstractObjectNormalizer::MAX_DEPTH_HANDLER    => $handler,
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => $handler,
        ];

        return $parameters;
    }
}