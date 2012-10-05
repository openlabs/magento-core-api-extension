<?php
/**
 *
 * @author     Mohammed NAHHAS
 * @package Openlabs_OpenERPConnector
 *
 */

/* Retrieve the version of Magento */
$version = Mage::getVersion();

/* Need to add a sales_order attribute if the version < 1.4.x.x - sales_order is an EAV Model */
/* Else add a simple column to sales_flat_order Table */
$version = str_replace('.','',$version);
if($version >= 1400) {
    $installer = $this;
    $installer->startSetup();
    $tableName='sales_flat_order';
    $db = $installer->getConnection();

    /* Delete the column if it exists before re-create it */
    if($db->tableColumnExists($this->getTable($tableName), 'imported')) {
        $installer->run("ALTER TABLE {$this->getTable($tableName)} drop column imported");
    }

    /* re-create 'imported' column */
    if(!$db->tableColumnExists($this->getTable($tableName), 'imported')) {
        $installer->run("
            ALTER TABLE {$this->getTable($tableName)} ADD imported tinyint(1) unsigned NOT NULL default '0';
        ");
    }
}else{
    $installer = new Mage_Eav_Model_Entity_Setup('core_setup');
    $installer->startSetup();

    /* Attribute Data */
    $attribute_data  = array(
                            'type'          => 'int',
                            'default'       => 0,
                            'visible'       => false,
                            'required'      => false,
                            'user_defined'  => false,
                            'searchable'    => false,
                            'filterable'    => false,
                            'comparable'    => false
                            );
    /* Add a sales_order attribute named 'imported' */
    $installer->addAttribute('order', 'imported', $attribute_data);

}


/* End */
$installer->endSetup();
