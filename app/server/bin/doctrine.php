<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

$cmsCliConfigFile = realpath((dirname(__FILE__) . '/../application/configs/cli-config.php'));
$cmsConfig = require($cmsCliConfigFile);

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine', BASE_PATH . '/library');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Symfony', BASE_PATH . '/library/Doctrine');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Seitenbau', BASE_PATH . '/library');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Cms', BASE_PATH . '/library');
$classLoader->register();

$dcConfig = new \Doctrine\ORM\Configuration();
$dcConfig->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$dcConfig->setProxyDir(BASE_PATH . '/library/Orm/Proxies');
$dcConfig->setProxyNamespace('Orm\Proxies');
$dcConfig->setEntityNamespaces(array('Orm\Entity'));
$dcConfig->setMetadataDriverImpl(
  new \Doctrine\ORM\Mapping\Driver\StaticPHPDriver(realpath(BASE_PATH . '/library/Orm/Entity'))
);

// create db connection (use Zend_Db like at bootstrap)
require_once('Zend/Db.php');
$dbConfig = $cmsConfig['db'];
$dbAdapter = \Zend_Db::factory($dbConfig['adapter'], $dbConfig);
$connectionOptions = array(
  'pdo' => $dbAdapter->getConnection(),
  'dbname' => $dbConfig['dbname'],
);

// create entity manager
$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $dcConfig,
  new Doctrine\Common\EventManager()
);

// Create OutputWriter
$output = new \Symfony\Component\Console\Output\ConsoleOutput();
$migrationOutputWriter = new \Doctrine\DBAL\Migrations\OutputWriter(function($message) use ($output) {
  $output->writeln($message);
});

// create migration commands
$migrationCommands = array(
  // Migrations Commands
  new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
  new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
  new \Doctrine\DBAL\Migrations\Tools\Console\Command\LatestCommand(),
  new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
  new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
  new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand(),
  new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
);
// set config to migration commands
$configuration = new \Doctrine\DBAL\Migrations\Configuration\Configuration($em->getConnection(),
  $migrationOutputWriter);
$configuration->setMigrationsTableName('migrations');
$configuration->setMigrationsNamespace('Orm\Migrations');
$configuration->setMigrationsDirectory(BASE_PATH . '/library/Orm/Migrations/');
$configuration->registerMigrationsFromDirectory(BASE_PATH . '/library/Orm/Migrations/');
foreach ($migrationCommands as $command) {
  $command->setMigrationConfiguration($configuration);
}

// Create and run ConsoleRunner
$helperSet = \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($em);
$helperSet->set(new \Symfony\Component\Console\Helper\DialogHelper(), 'dialog');
$cli = \Doctrine\ORM\Tools\Console\ConsoleRunner::createApplication($helperSet, $migrationCommands);
$cli->run(null, $output);
