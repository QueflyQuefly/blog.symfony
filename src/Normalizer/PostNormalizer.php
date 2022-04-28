<?php

namespace App\Normalizer;

use App\Entity\Post;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PostNormalizer implements ContextAwareNormalizerInterface
{
    private $router;
    private $normalizer;

    public function __construct(UrlGeneratorInterface $router, ObjectNormalizer $normalizer)
    {
        $this->router = $router;
        $this->normalizer = $normalizer;
    }

    public function normalize($post, string $format = null, array $context = []): array
    {
        $data = [
            'id'             => $post->getId(),
            'user_fio'       => $post->getUser()->getFio(),
            'date_time'      => $post->getDateTime(),
            'title'          => $post->getTitle(),
            'content'        => $post->getContent(),
            'rating'         => $post->getRating(),
            'count_comments' => $post->getCountComments(),
            'count_ratings'  => $post->getCountRatingPosts(),
        ];

        return $data;
    }

    public function normalizeArrayOfPosts(array $posts): array
    {
        $data = [];

        foreach ($posts as $post) {
            $data[] = $this->normalize($post);
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Post;
    }
}