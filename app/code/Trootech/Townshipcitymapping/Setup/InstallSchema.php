<?php

namespace Trootech\Townshipcitymapping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){

		$installer->run('DROP TABLE IF EXISTS `township_city_combination`');
$installer->run('CREATE TABLE `township_city_combination` (
  `township_id` int NOT NULL AUTO_INCREMENT,
  `city_id` text NOT NULL,
  `township_name` varchar(120) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `country_name` varchar(123) NOT NULL,
  PRIMARY KEY (`township_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');


		

		}

        $installer->endSetup();

    }
}