<?php
/**
 * Created because the regular expression causes some issues with the ERP. When you have 
 * numeric references (SKUs) such expresion (only try to find products by sku when it is not numeric)
 * causes issues.
 *
 * @author Daniel Lozano Morales dn.lozano.m@gmail.com
 */
class Openlabs_OpenERPConnector_Helper_Product extends Mage_Catalog_Helper_Product
{
    /**
     * Return loaded product instance
     *
     * @param  int|string $productId (SKU or ID)
     * @param  int $store
     * @param  string $identifierType
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($productId, $store, $identifierType = null)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore($store)->getId());

        $expectedIdType = false;
        if ($identifierType === null) {
        	/**
        	 * I didnt deleted the regular expression, but i commented it.
        	 * It used to cause issues when your Magento SKU is numeric because Magento treats it as an integer.
        	 * @author  Daniel Lozano Morales dn.lozano.m@gmail.com
        	 */
            if (is_string($productId) /*&& !preg_match("/^[+-]?[1-9][0-9]*$|^0$/", $productId)*/) {
                $expectedIdType = 'sku';
            }
        }

        if ($identifierType == 'sku' || $expectedIdType == 'sku') {
            $idBySku = $product->getIdBySku($productId);
            if ($idBySku) {
                $productId = $idBySku;
            } else if ($identifierType == 'sku') {
                // Return empty product because it was not found by originally specified SKU identifier
                return $product;
            }
        }

        if ($productId && is_numeric($productId)) {
            $product->load((int) $productId);
        }

        return $product;
    }	
}