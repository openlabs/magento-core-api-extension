<?php

/**
 *
 * @author     Mohammed NAHHAS
 * @package Openlabs_OpenERPConnector
 *
 */

class Openlabs_OpenERPConnector_Model_Sales_Order_Api extends Mage_Sales_Model_Order_Api {

    /**
     * Return the list of products ids that match with the filter
     * The filter imported is required
     * @param  array
     * @return array
     */
    public function search($data) {

        $result = array();
        if(isset($data['imported'])) {

            $collection = Mage::getModel("sales/order")->getCollection()
                ->addAttributeToSelect('increment_id')
                ->addAttributeToFilter('imported', array('eq' => $data['imported']));

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
                $result[] =  $order['increment_id'];
            }

            return $result;
        }else{
            $this->_fault('data_invalid', "Error, the attribut 'imported' need to be specified");
        }
    }


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
                ->addAttributeToFilter('imported', array('eq' => $data['imported']));

            /* addAddressFields() is called only if version >= 1400 */
            if(str_replace('.','',Mage::getVersion()) >= 1400) {
                $collection->addAddressFields();
            }

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
                $tmp = $this->_getAttributes($order, 'order');

                /* if version < 1400, billing and shipping information are added manually to order data */
                if(str_replace('.','',Mage::getVersion()) < 1400) {
                    $address_data = $this->_getAttributes($order->getShippingAddress(), 'order_address');
                    if(!empty($address_data)) {
                        $tmp['shipping_firstname'] = $address_data['firstname'];
                        $tmp['shipping_lastname'] = $address_data['lastname'];
                    }

                    $address_data = $this->_getAttributes($order->getBillingAddress(), 'order_address');
                    if(!empty($address_data)) {
                        $tmp['billing_firstname'] = $address_data['firstname'];
                        $tmp['billing_lastname'] = $address_data['lastname'];
                    }
                }

                $result[] = $tmp;
            }
            return $result;
        }else{
            $this->_fault('data_invalid', "Error, the attribut 'imported' need to be specified");
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

    /* Retrieve invoices increment ids of the order */
    public function getInvoiceIds($incrementId) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        /**
          * Check order existing
        */
        if (!$order->getId()) {
             $this->_fault('order_not_exists');
        }
        $res = array();
        foreach($order->getInvoiceCollection() as $invoice){
            array_push($res, $invoice->getIncrementId());
        };
        return $res;
    }

    /* Retrieve shipment increment ids of the order */
    public function getShipmentIds($incrementId) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        /**
          * Check order existing
        */
        if (!$order->getId()) {
             $this->_fault('order_not_exists');
        }
        $res = array();
        foreach($order->getShipmentsCollection() as $shipping){
            array_push($res, $shipping->getIncrementId());
        };
        return $res;
    }

}
