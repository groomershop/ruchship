<?php 

namespace Magento\RuchShip\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Create Ruch data columns in quote and order tables
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$setup->startSetup();
		$tableName = $setup->getTable('quote');
		
		if(version_compare($context->getVersion(), '2.0.0', '<')) {
    		$setup->getConnection()->addColumn(
    			$tableName,
    			'ruch_point',
    			[
    			    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    				'nullable' => true,
    				'default' => 0,
    				'comment' => 'Ruch point ID'
    			]
    		);
		}
		if(version_compare($context->getVersion(), '3.0.0', '<')) {
		    $setup->getConnection()->addColumn(
		        $tableName,
		        'ruch_type',
		        [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'length' => '1',
		            'default' => 'R',
		            'comment' => 'Ruch point type'
		        ]
		    );
		    $setup->getConnection()->addColumn(
		        $tableName,
		        'ruch_param',
		        [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'length' => '1',
		            'comment' => 'Ruch point additional param'
		        ]
		    );
		}
		if(version_compare($context->getVersion(), '3.3.0', '<')) {
		    $setup->getConnection()->addColumn(
		        $tableName,
		        'ruch_desc',
		        [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'length' => '1024',
		            'comment' => 'Ruch point description'
		        ]
	        );
		}
		elseif(version_compare($context->getVersion(), '3.4.3', '<')) {
		    $setup->getConnection()->modifyColumn(
		        $tableName,
		        'ruch_desc',
		        [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'length' => '1024',
		            'comment' => 'Ruch point description'
		        ]
		        );
		}
		if(version_compare($context->getVersion(), '3.3.6', '<')) {
		    $setup->getConnection()->addColumn(
		        $tableName,
		        'ruch_destinationcode',
		        [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'length' => '15',
		            'comment' => 'Ruch destination code'
		        ]
		    );
		};
		if(version_compare($context->getVersion(), '3.4.0', '<')) {        
		    $tableName = $setup->getTable('sales_order');
		    $setup->getConnection()->addColumn(
		        $tableName,
		        'ruch_destinationcode',
		        [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'length' => '15',
		            'comment' => 'Ruch destination code'
		        ]
		    );		    
		}
		
		$setup->endSetup();
	}
}

?>