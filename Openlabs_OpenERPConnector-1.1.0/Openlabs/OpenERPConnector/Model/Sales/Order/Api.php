<?php

/**
 * 
 * @author 	Mohammed NAHHAS
 * @package Openlabs_OpenERPConnector
 *
 */

class Openlabs_OpenERPConnector_Model_Sales_Order_Api extends Mage_Sales_Model_Order_Api {
	
	/**
	 * 
	 * Retrieve orders data based on the value of the flag 'imported'
	 * @param  array
	 * @return array
	 */
	public function retrieveOrders($data) {
			        
	    $result = array();
		if(isset($data['imported'])) {
	        
	        $collection = Mage::getModel("sales/order")->getCollection()
	            ->addAttributeToSelect('*')
	            ->addAttributeToFilter('imported', array('eq' => $data['imported']))
	            ->addAddressFields();
	            
	        if(isset($data['limit'])) {
	        	$collection->setPageSize($data['limit']);
	        	$collection->setOrder('entity_id', 'ASC');
	        }
	        
	        if(isset($data['filters']) && is_array($data['filters'])) {
	        	$filters = $data['filters'];
	        	foreach($filters as $field => $value) {
	        		$collection->addAttributeToFilter($field, $value);
	        	}
	        }

	        foreach ($collection as $order) {
	            $result[] = $this->_getAttributes($order, 'order');
	        }
	        return $result;
		}else{
			$this->_fault('data_invalid', "erreur, l'attribut 'imported' doit être spécifié");
		}
	}
	
	public function setFlagForOrder($incrementId) {
		$_order = $this->_initOrder($incrementId);
		$_order->setImported(1);
		try {
			$_order->save();
			return true;
		} catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
	}
	
	/* Retrieve increment_id of the child order */
    public function getOrderChild($incrementId) {
    	
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        /**
          * Check order existing
          */
        if (!$order->getId()) {
             $this->_fault('order_not_exists');
        }
        
        if($order->getRelationChildId()) {
        	return $order->getRelationChildRealId();
        }else{
        	return false;
        }
    }
    
    /* Retrieve increment_id of the parent order */
    public function getOrderParent($incrementId) {
    	
    	$order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        /**
          * Check order existing
          */
        if (!$order->getId()) {
             $this->_fault('order_not_exists');
        }
        
        if($order->getRelationParentId()) {
        	return $order->getRelationParentRealId();
        }else{
        	return false;
        }
    }
}