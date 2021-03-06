<?php

use App\Kernel;
// use Symfony\Component\Cache\Adapter\RedisAdapter;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

/* There are commands to activate Doctrine cache --deprecated */
// $config = new \Doctrine\ORM\Configuration();
// $cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_queries', 60);
// $config->setQueryCache($cache);
// $cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_results', 60);
// $config->setResultCache($cache);
// $cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_metadata', 60);
// $config->setMetadataCache($cache);

/* There are commands to activate second level cache in Doctrine */
// /** @var \Psr\Cache\CacheItemPoolInterface $cache */
// $cacheConfig = new \Doctrine\ORM\Cache\RegionsConfiguration();
// $factory = new \Doctrine\ORM\Cache\DefaultCacheFactory($cacheConfig, $cache);
// $config->setSecondLevelCacheEnabled();
// $cacheConfig  =  $config->getSecondLevelCacheConfiguration();
// $cacheConfig->setCacheFactory($factory);
// $regionConfig =  $cacheConfig->getRegionsConfiguration();
// $regionConfig->setDefaultLifetime(3600);

/* There are settings to configure RedisAdapter */
// $client = RedisAdapter::createConnection(
//     'redis://localhost:6379',
//     [
//         'lazy' => false,
//         'persistent' => 0,
//         'persistent_id' => null,
//         'tcp_keepalive' => 0,
//         'timeout' => 30,
//         'read_timeout' => 0,
//         'retry_interval' => 0,
//     ]
// );

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
