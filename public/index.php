<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

$config = new \Doctrine\ORM\Configuration();
$cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_queries', 60);
$config->setQueryCache($cache);
$cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_results', 60);
$config->setResultCache($cache);
$cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_metadata', 60);
$config->setMetadataCache($cache);


/** @var \Psr\Cache\CacheItemPoolInterface $cache */

$cacheConfig = new \Doctrine\ORM\Cache\RegionsConfiguration();
$factory = new \Doctrine\ORM\Cache\DefaultCacheFactory($cacheConfig, $cache);

// Enable second-level-cache
$config->setSecondLevelCacheEnabled();

// Cache factory
$cacheConfig  =  $config->getSecondLevelCacheConfiguration();
$cacheConfig->setCacheFactory($factory);

$regionConfig =  $cacheConfig->getRegionsConfiguration();

// Cache Region lifetime
$regionConfig->setDefaultLifetime(7200);

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
