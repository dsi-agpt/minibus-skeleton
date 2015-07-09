<?php
// bootstrap.php
require_once __DIR__ . "/../vendor/autoload.php";
// insert
Zend\Loader\AutoloaderFactory::factory(array(
    'Zend\Loader\StandardAutoloader' => array(
        Zend\Loader\StandardAutoloader::LOAD_NS => array(
            "Minibus" => __DIR__ . "/../vendor/dsi-agpt/minibus/src/Minibus",
            "Jobs" => __DIR__ . "/../module/Jobs/src/Jobs"
        )
    )
));
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

function GetEntityManager()
{
    $paths = array(
        __DIR__ . '/../vendor/dsi-agpt/minibus/src/Minibus/Model/Entity',
        __DIR__ . '/../module/Jobs/src/Jobs/Model/Entity'
    );
    $isDevMode = false;
    
    // the connection configuration
    $localConfig = include __DIR__ . '/autoload/minibus.local.php';
    $dbParams = $localConfig['doctrine']['connection']['orm_default']['params'];
    
    $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
    $entityManager = EntityManager::create($dbParams, $config);
    $platform = $entityManager->getConnection()->getDatabasePlatform();
    $platform->registerDoctrineTypeMapping('enum', 'string');
    return $entityManager;
}
