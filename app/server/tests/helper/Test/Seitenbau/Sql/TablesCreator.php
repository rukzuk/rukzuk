<?php
namespace Test\Seitenbau\Sql;

use Test\Seitenbau\Reader as AbstractReader;

/**
 * TablesCreator
 *
 * @package      Test
 * @subpackage   Sql
 */
class TablesCreator
{
  /**
   * @var array
   */
  private $connectionOptions;
  /**
   *
   * @var Doctrine\Orm\EntityManager
   */
  private $entityManager;
  
  /**
   * @var type 
   */
  private $modelPath;
  
  
  public function __construct(array $connectionOptions)
  {
    $this->connectionOptions = $connectionOptions;
    $this->initDoctrine();
  }
  
  private function dropAll()
  {    
    if (!isset($this->connectionOptions['path'])) {
      return;  
    }
    $metadataClasses = $this->getMetadataClassesOfEntities();
    if (count($metadataClasses) > 0) {
      $tool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
      $tool->dropSchema($metadataClasses);
    }
  }
  
  public function fromEntities()
  {
    $metadataClasses = $this->getMetadataClassesOfEntities();
    if (count($metadataClasses) > 0) {
      $this->dropAll();
      $tool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
      $tool->createSchema($metadataClasses);
    }
  }
  
  private function initDoctrine()
  {
    $this->modelPath = APPLICATION_PATH . '/configs/models';
    $classLoader = new \Doctrine\Common\ClassLoader(
      'Entities', 
      $this->modelPath
    );
    $classLoader->register();
    $classLoader = new \Doctrine\Common\ClassLoader(
      'Symfony', 
      BASE_PATH . '/library/Doctrine'
    );
    $classLoader->register();

    $config = new \Doctrine\ORM\Configuration();
    $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
    $driverImpl = $config->newDefaultAnnotationDriver($this->modelPath);
    $config->setMetadataDriverImpl($driverImpl);
    $config->setProxyDir(BASE_PATH . '/library/Orm/Proxies');
    $config->setProxyNamespace('\Orm\Proxies');

    $em = \Doctrine\ORM\EntityManager::create(
      $this->connectionOptions, 
      $config
    );

    $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
      'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
      'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
    ));
    $this->entityManager = $em;
  }
  
  /**
   * @return array
   */
  private function getMetadataClassesOfEntities()
  {
    $tool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);

    $dir = new \DirectoryIterator($this->modelPath);
    $metadatas = array();
    
    foreach ($dir as $fileinfo) {
      if (!$fileinfo->isDot()) {
        $modelPathInfo = pathinfo($fileinfo->getFilename());
        $entity = 'Orm\\Entity\\' . $modelPathInfo['filename'];
        $metadatas[] = $this->entityManager->getClassMetadata($entity);
      }
    }

    return $metadatas;
  }
}