<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

$app = new Application();

$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});
$app['web_dir'] = __DIR__;
$app['em_user'] =  new \Provider\FileDataBase($app, 'User');


$loader = new \Symfony\Component\Routing\Loader\YamlFileLoader(new \Symfony\Component\Config\FileLocator(__DIR__ . DIRECTORY_SEPARATOR));
$collection = $loader->load('routes.yml');
$app['routes']->addCollection($collection);

return $app;
