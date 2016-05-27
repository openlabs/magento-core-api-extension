<?php
/**
 *
 * @author     Mohammed NAHHAS
 * @package Openlabs_OpenERPConnector
 *
 */

class Openlabs_OpenERPConnector_Model_Observer extends Mage_Core_Model_Abstract 
{
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
    /**
     * Función para solucionar el problema de importación con el coste de pago por contra reembolso.
     * La solución consiste en añadir una línea al pedido, de un producto oculto.
     */
    public function addCashOnDelivery($observer)
    {
        $order = $observer->getOrder();
        $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode();

        if ($paymentMethodCode == 'msp_cashondelivery')
        {
            $fakeProductId = Mage::getModel('catalog/product')->getIdBySku('CASHONDELIVERY');

            if ($fakeProductId)
            {
                $fakeProduct = Mage::getModel('catalog/product')->load($fakeProductId);

                $attributes = $order->getData();
                $cashOnDeliveryFee = (float) $attributes['msp_cashondelivery'];
                $cashOnDeliveryFee = number_format($cashOnDeliveryFee, 4, '.', '');

                $orderItem = Mage::getModel('sales/order_item')
                    ->setStoreId($order->getStoreId())
                    ->setQuoteItemId(0)
                    ->setQuoteParentItemId(NULL)
                    ->setProductId($fakeProduct->getId())
                    ->setProductType($fakeProduct->getTypeId())
                    ->setQtyBackordered(NULL)
                    ->setQtyOrdered(1)
                    ->setTotalQtyOrdered(1)
                    ->setName($fakeProduct->getName())
                    ->setSku($fakeProduct->getSku())
                    ->setPrice($cashOnDeliveryFee)
                    ->setBasePrice($cashOnDeliveryFee)
                    ->setOriginalPrice($cashOnDeliveryFee)
                    ->setRowTotal($cashOnDeliveryFee)
                    ->setBaseRowTotal($cashOnDeliveryFee);

                $order->addItem($orderItem);
                $order->save();
            }
        }
    }
}