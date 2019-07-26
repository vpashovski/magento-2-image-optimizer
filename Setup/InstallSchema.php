<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_ImageOptimizer
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ImageOptimizer\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\ImageOptimizer\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('mageplaza_image_optimizer')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_image_optimizer'))
                ->addColumn('image_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Banner Id')
                ->addColumn('path', Table::TYPE_TEXT, 255, [], 'Name')
                ->addColumn('status', Table::TYPE_TEXT, 255, [], 'Status')
                ->addColumn('origin_size', Table::TYPE_INTEGER, null, [], 'Original Size')
                ->addColumn('optimize_size', Table::TYPE_INTEGER, null, [], 'Optimize Size')
                ->addColumn('percent', Table::TYPE_INTEGER, null, [], 'Percent')
                ->addColumn('message', Table::TYPE_TEXT, '2M', [], 'Message')
                ->addIndex($installer->getIdxName('mageplaza_image_optimizer', ['status']), ['status']);

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
