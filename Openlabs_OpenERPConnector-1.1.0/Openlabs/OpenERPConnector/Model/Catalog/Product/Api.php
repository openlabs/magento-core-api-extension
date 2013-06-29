<?php

/**
 * @author     Mohammed NAHHAS
 * @package Openlabs_OpenERPConnector
 */

class Openlabs_OpenERPConnector_Model_Catalog_Product_Api extends Mage_Catalog_Model_Product_Api
{
    /**
     *  Set additional data before product saved
     *
     *  @param    Mage_Catalog_Model_Product $product
     *  @param    array $productData
     *  @return      object
     */
    protected function _prepareDataForSave ($product, $productData)
    {
        if (isset($productData['categories']) && is_array($productData['categories'])) {
            $product->setCategoryIds($productData['categories']);
        }

        if (isset($productData['websites']) && is_array($productData['websites'])) {
            foreach ($productData['websites'] as &$website) {
                if (is_string($website)) {
                    try {
                        $website = Mage::app()->getWebsite($website)->getId();
                    } catch (Exception $e) { }
                }
            }
            $product->setWebsiteIds($productData['websites']);
        }

        if (Mage::app()->isSingleStoreMode()) {
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        if (isset($productData['stock_data']) && is_array($productData['stock_data'])) {
            $product->setStockData($productData['stock_data']);
        } else {
            $product->setStockData(array('use_config_manage_stock' => 0));
        }

        if (isset($productData['tier_price']) && is_array($productData['tier_price'])) {
             $tierPrices = Mage::getModel('catalog/product_attribute_tierprice_api')->prepareTierPrices($product, $productData['tier_price']);
             $product->setData(Mage_Catalog_Model_Product_Attribute_Tierprice_Api::ATTRIBUTE_CODE, $tierPrices);
        }

        /*
         * Check if configurable product data array passed
         */
        if(isset($productData['configurable_products_data']) && is_array($productData['configurable_products_data'])) {
            $product->setConfigurableProductsData($productData['configurable_products_data']);
        }

        if(isset($productData['configurable_attributes_data']) && is_array($productData['configurable_attributes_data'])) {
            foreach($productData['configurable_attributes_data'] as $key => $data) {

                //Check to see if these values exist, otherwise try and populate from existing values
                $data['label']             =    (!empty($data['label']))             ? $data['label']             : $product->getResource()->getAttribute($data['attribute_code'])->getStoreLabel();
                $data['frontend_label'] =    (!empty($data['frontend_label']))     ? $data['frontend_label']     : $product->getResource()->getAttribute($data['attribute_code'])->getFrontendLabel();
                $productData['configurable_attributes_data'][$key] = $data;
            }
            $product->setConfigurableAttributesData($productData['configurable_attributes_data']);
            $product->setCanSaveConfigurableAttributes(true);
        }

        /*
         * Check if bundle product data, options and bundle items arrays passed
         */
        if(isset($productData['bundle_items_data']) && isset($productData['options_data']) && is_array($productData['bundle_items_data']) && is_array($productData['options_data'])) {

            $product->setBundleOptionsData($productData['options_data']);
            $product->setBundleSelectionsData($productData['bundle_items_data']);
            $product->setCanSaveBundleSelections(true);
            $product->setAffectBundleProductSelections(true);
            Mage::register('product', $product);  // product must be registred in order to get the store_id, see _beforeSave() in Mage/Bundle/Model/Selection.php
        }
    }
}