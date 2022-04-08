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
        RedisRepository $redisRepository,
        SerializerInterface $serializer
    ) {
        $this->redisRepository = $redisRepository;
        $this->serializer = $serializer;
    }

    public function get(string $key, int $ttl, string $type, callable $function)
    {
        if (empty($value = $this->redisRepository->get($key))) {
            $value = $function();
            $this->redisRepository->set($key, $ttl, $this->serializer->serialize($value, 'json', [
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                AbstractObjectNormalizer::MAX_DEPTH_HANDLER => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                    return $innerObject->getId();
                },
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return $object->getId();
                }
            ]));
        } else {
            $value = $this->serializer->deserialize($value, $type, 'json' , [
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                AbstractObjectNormalizer::MAX_DEPTH_HANDLER => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                    return $innerObject->getId();
                },
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return $object->getId();
                }
            ]);
        }

        return $value;
    }
}