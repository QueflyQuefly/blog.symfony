<?php

namespace App\Normalizer;

use App\Entity\Post;
use Exception;

class PostNormalizer
{
    public function normalize($post): array
    {
        if (! $this->supportsNormalization($post)) {
            throw new Exception('It is not a Post object. Normalization is doen\'t supported');
        }

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

    public function supportsNormalization($data): bool
    {
        return $data instanceof Post;
    }
}