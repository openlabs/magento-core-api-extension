<?php

class Openlabs_OpenERPConnector_Model_Olcatalog_Product_Observer_Bundle
{
    public function addBundleInfo($observer)
    {
        $productId = $observer->getEvent()->getProductId();
        $resultObject = $observer->getEvent()->getResultObject();
        $store = $observer->getEvent()->getStore();
        $store = Mage::app()->getStore($store);

        $bundleOptions = array();

        $options = Mage::getResourceModel('bundle/option_collection')->setProductIdFilter($productId)
            ->joinValues($store->getId());
        if ($options->count())
        {
            $bundleOptions['options'] = array();
            foreach($options as $option)
            {
                /* @var $selections Mage_Bundle_Model_Resource_Selection_Collection */
                $selections = Mage::getResourceModel('bundle/selection_collection');
                $selections->joinPrices($store->getId());
                $selections->setOptionIdsFilter(array($option->getid()));

                /* @var $option Mage_Bundle_Model_Option */
                $optionData = $option->getData();
                // pas besoin du parent_id
                unset($optionData['parent_id']);

                $optionData['selections'] = array();

                foreach($selections as $selection)
                {
                    /* @var $selection Mage_Bundle_Model_Selection */
                    $selectionData = array();

                    foreach(array('selection_id','position', 'sku', 'product_id', 'is_default', 'selection_price_type',
                                'selection_price_value', 'selection_qty', 'selection_can_change_qty') as $key)
                    {
                        $selectionData[$key] = $selection->getData($key);
                        if ($key == 'selection_price_type')
                        {
                            $selectionData[$key] = $selectionData[$key] == '1' ? 'percent' : 'fixed';
                        }
                    }

                    $optionData['selections'][] = $selectionData;
                }

                $bundleOptions['options'][] = $optionData;
            }
        }

        $resultObject->setData('_bundle_data', $bundleOptions);
    }
}