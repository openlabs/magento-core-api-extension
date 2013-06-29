<?php
/**
 *
 * @author     Mohammed NAHHAS
 * @package Openlabs_OpenERPConnector
 *
 */

class Openlabs_OpenERPConnector_Model_Observer extends Mage_Core_Model_Abstract {

    /* Initialize attribtue 'imported' if version < 1.4.0.0 */
    public function initImported($observer) {
        if(str_replace('.','',Mage::getVersion()) < 1400) {
            $order = $observer->getOrder();
            try {
                $order->setImported(0)->save();
            }catch (Exception $e) {
                /* If logs are enabled (backend : system->configuration->developer->logSettings), it creates a file named OpenErp_Connector.log in /var/log/ which contains the errors */
                Mage::log('Error, order increment_id = '.$order->getIncrementId().', attribute "imported"  was not initialized - error : '.$e->getMessage(), null, 'OpenErp_Connector.log');
            }
            $order->setImported(0)->save();
        }
    }
}