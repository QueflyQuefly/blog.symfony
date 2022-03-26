<?php

use App\Kernel;
use Symfony\Contracts\Cache\ItemInterface;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

$config = new \Doctrine\ORM\Configuration();
$cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_queries', 3600);
$config->setQueryCache($cache);
$cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_results', 3600);
$config->setResultCache($cache);
$cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_metadata', 3600);
$config->setMetadataCache($cache);

/** @var \Doctrine\ORM\Cache\RegionsConfiguration $cacheConfig */
/** @var \Psr\Cache\CacheItemPoolInterface $cache */
/** @var \Doctrine\ORM\Configuration $config */

$cacheConfig = new \Doctrine\ORM\Cache\RegionsConfiguration();
$factory = new \Doctrine\ORM\Cache\DefaultCacheFactory($cacheConfig, $cache);

// Enable second-level-cache
$config->setSecondLevelCacheEnabled();

// Cache factory
$config->getSecondLevelCacheConfiguration()
    ->setCacheFactory($factory)
;
$cacheConfig  =  $config->getSecondLevelCacheConfiguration();
$regionConfig =  $cacheConfig->getRegionsConfiguration();

// Cache Region lifetime
$regionConfig->setDefaultLifetime(7200);

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
