<?php

/**
 *
 * @author     Mohammed NAHHAS
 * @package Openlabs_OpenERPConnector
 *
 */

class Openlabs_OpenERPConnector_Adminhtml_InitController extends Mage_Adminhtml_Controller_Action
{
    protected $_imported = 'imported';

    /* Initialize the Attribute 'Imported' for orders placed before installing Openlabs_OpenERPConnector Extension */
    public function ordersAction() {
        /* 'imported' value will be initialized if magento version is < 1.4.0.0 */
        if(str_replace('.','',Mage::getVersion()) < 1400) {
            $orders = array();

            /* 'imported' attribute values are stored in sales_order_int */
            $imported_attribute_table_name = 'sales_order_int';

            /* retrieve entity_type_id for order */
            $entity_type = Mage::getModel('eav/entity_type')->loadByCode('order');

            /* Load 'imported' attribute to get its attribute_id */
            $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
                            ->setCodeFilter($this->_imported)
                            ->setEntityTypeFilter($entity_type->getEntityTypeId())
                            ->getFirstItem();

            /* load order collection */
            $collection = Mage::getResourceModel('sales/order_collection')
                            ->addAttributeToSelect($this->_imported);

            if(count($collection->getItems()) > 0) {
                foreach($collection as $order) {
                    try{
                        $test = $order->getResource()->getAttribute($this->_imported);
                        $orders[] = $order->getIncrementId();
                        $order->setImported(0)->save();
                        $request     = "INSERT IGNORE INTO ".$imported_attribute_table_name." (entity_type_id, attribute_id, entity_id, value) VALUES (".$entity_type->getEntityTypeId().", ".$attributeInfo->getAttributeId().", ".$order->getEntityId().", 0)";
                        $write         = Mage::getSingleton('core/resource')->getConnection('core_write');
                        $query         = $write->query($request);
                    }catch (Exception $e) {
                        echo 'Error : '.$e->getMessage();
                    }
                }

                echo 'Number of Orders Initialized : '.count($collection->getItems()) .'<br />';
                echo 'Orders List : '.'<br />';
                echo '<pre>';
                print_r($orders);
                echo '</pre>';
            }
        }else{
            echo 'Magento Version : '.Mage::getVersion().'<br />';
            echo 'There is no need to initialize orders';
        }
    }
}