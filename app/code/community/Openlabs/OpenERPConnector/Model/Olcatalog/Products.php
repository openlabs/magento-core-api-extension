<?php

class Openlabs_OpenERPConnector_Model_Olcatalog_Products extends Mage_Catalog_Model_Api_Resource
{
    protected $_filtersMap = array(
        'product_id' => 'entity_id',
        'set'        => 'attribute_set_id',
        'type'       => 'type_id'
    );

    public function __construct()
    {
        $this->_storeIdSessionField = 'product_store_id';
        $this->_ignoredAttributeTypes[] = 'gallery';
        $this->_ignoredAttributeTypes[] = 'media_image';
    }

    /**
     * Return the list of products ids
     *
     * @param array $filters
     * @param string|int $store
     * @return array
     */
    public function search($filters = null, $store = null)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($this->_getStoreId($store))
            ->addAttributeToSelect('name');

        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_filtersMap[$field])) {
                        $field = $this->_filtersMap[$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $product) {
            $result[] = $product->getId();
        }

        return $result;
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param array $filters
     * @param string|int $store
     * @return array
     */
    public function items($filters = null, $store = null)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($this->_getStoreId($store))
            ->addAttributeToSelect('name');

        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_filtersMap[$field])) {
                        $field = $this->_filtersMap[$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $product) {
//            $result[] = $product->getData();
            $result[] = array( // Basic product data
                'product_id' => $product->getId(),
                'sku'        => $product->getSku(),
                'name'       => $product->getName(),
                'set'        => $product->getAttributeSetId(),
                'type'       => $product->getTypeId(),
                'category_ids'       => $product->getCategoryIds()
            );
        }

        return $result;
    }

    /**
     * Retrieve product info
     *
     * @param int|string $productId
     * @param string|int $store
     * @param array $attributes
     * @return array
     */
    public function biglist($productId, $store = null, $filters = null)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($this->_getStoreId($store))
            ->addAttributeToSelect('name');

        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_filtersMap[$field])) {
                        $field = $this->_filtersMap[$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }
        foreach ($collection as $product) {
            $data = array();
            $data = array( // Basic product data
                'product_id' => $product->getId(),
                'sku'        => $product->getSku(),
                'set'        => $product->getAttributeSetId(),
                'type'       => $product->getTypeId(),
                'categories' => $product->getCategoryIds(),
                'websites'   => $product->getWebsiteIds()
            );

            foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
                    $data[$attribute->getAttributeCode()] = $product->getData($attribute->getAttributeCode());
            }
            $result[]=$data;
        }
        return $result;
    }

    /**
     * Create new product.
     *
     * @param string $type
     * @param int $set
     * @param array $productData
     * @return int
     */
    public function create($type, $set, $sku, $productData)
    {
        if (!$type || !$set || !$sku) {
            $this->_fault('data_invalid');
        }

        $product = Mage::getModel('catalog/product');
        /* @var $product Mage_Catalog_Model_Product */
        $product->setStoreId($this->_getStoreId($store))
            ->setAttributeSetId($set)
            ->setTypeId($type)
            ->setSku($sku);

        if (isset($productData['website_ids']) && is_array($productData['website_ids'])) {
            $product->setWebsiteIds($productData['website_ids']);
        }

        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute)
                && isset($productData[$attribute->getAttributeCode()])) {
                $product->setData(
                    $attribute->getAttributeCode(),
                    $productData[$attribute->getAttributeCode()]
                );
            }
        }

        $this->_prepareDataForSave($product, $productData);

        if (is_array($errors = $product->validate())) {
            $this->_fault('data_invalid', implode("\n", $errors));
        }

        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return $product->getId();
    }

    /**
     * Update product data
     * 
     * @param int|string $productId
     * @param array $productData
     * @param string|int $store
     * @return boolean
     */
    public function update($productId, $productData = array(), $store = null)
    {
        /**
         * There is a problem with IDs, and SKUs. OpenERP sends the SKU as $productId's value, but maybe
         * this SKU has the same value than an existent product ID, so magento will write the product info.
         * on another product.
         * 
         * Adding a whitespace at the end of the product identifier, it's treated as a string.
         * Of this way, the magento helper will first try to find an ID with the SKU, and if nothing is found
         * it will try to use de ID to get the product directly
         * 
         * @author  Daniel Lozano Morales dn.lozano.m@gmail.com
         */
        $productId = (string) $productId;
        $product = $this->_getProduct($productId, $store);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        if (isset($productData['website_ids']) && is_array($productData['website_ids'])) {
            $product->setWebsiteIds($productData['website_ids']);
        }

        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute)
                && isset($productData[$attribute->getAttributeCode()])
                && $this->canUpdateAttribute($attribute) // Comprobar si está dentro de los atributos sincronizados
                ) {
                $product->setData(
                    $attribute->getAttributeCode(),
                    $productData[$attribute->getAttributeCode()]
                );
            }
        }

        $this->_prepareDataForSave($product, $productData);

        try {
            if (is_array($errors = $product->validate())) {
                $this->_fault('data_invalid', implode("\n", $errors));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

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

        if (isset($productData['stock_data']) && is_array($productData['stock_data'])) {
            $product->setStockData($productData['stock_data']);
        }
    }

    /**
     * Update product special price
     *
     * @param int|string $productId
     * @param float $specialPrice
     * @param string $fromDate
     * @param string $toDate
     * @param string|int $store
     * @return boolean
     */
    public function setSpecialPrice($productId, $specialPrice = null, $fromDate = null, $toDate = null, $store = null)
    {
        return $this->update($productId, array(
            'special_price'     => $specialPrice,
            'special_from_date' => $fromDate,
            'special_to_date'   => $toDate
        ), $store);
    }

    /**
     * Retrieve product special price
     *
     * @param int|string $productId
     * @param string|int $store
     * @return array
     */
    public function getSpecialPrice($productId, $store = null)
    {
        return $this->info($productId, $store, array('special_price', 'special_from_date', 'special_to_date'));
    }

    /**
     * TODO: Maybe here happens the same issue with sku/ids mess. Test it, but i think it will happen because ERP will send the SKU, and it will be treated as an ID.
     * @author  Daniel Lozano Morales dn.lozano.m@gmail.com
     * 
     * Delete product
     *
     * @param int|string $productId
     * @return boolean
     */
    public function delete($productId)
    {
        $product = $this->_getProduct($productId);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $product->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }
    
    public function info($productId, $store = null, $attributes = null, $identifierType = null)
    {
        $result = Mage::getSingleton('catalog/product_api')->info($productId, $store, $attributes, $identifierType);
        $resultObject = new Varien_Object($result);
        $type = $result['type'];
        Mage::dispatchEvent('ol_openerp_catalog_product_info_type_'.$type, array(
                'result_object' => $resultObject, 'product_id' => $productId, 'store' => $store));

        return $resultObject->getData();
    }

    /**
     * Comprobar si debemos actualizar o no estos atributos.
     * La versión 6.1 de OpenERP permite 'desincronizar' atributos, pero
     * sigue pisándolos por lo que hacemos la selección desde la parte 
     * de Magento.
     * 
     * @author Daniel Lozano Morales <daniel.lozano@juguetronica.com>
     * @param  Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return bool
     */
    private function canUpdateAttribute($attribute) {

        $ignoredAttrs = Mage::getStoreConfig('openerpconnector/productexport/attributes', Mage::app()->getStore());

        $attributeCode = $attribute->getData('attribute_code');

        if (strpos($ignoredAttrs, $attributeCode) > -1) {
            return false;
        }
        return true;
    }
} // Class Mage_Catalog_Model_Product_Api End