<?php

# -*- encoding: utf-8 -*-
###############################################################################
#
#   Webservice extension for Magento
#   Copyright (C) 2012-TODAY Akretion <http://www.akretion.com>. All Rights Reserved
#     @author Sébastien BEAU <sebastien.beau@akretion.com>
#   This program is free software: you can redistribute it and/or modify
#   it under the terms of the GNU Affero General Public License as
#   published by the Free Software Foundation, either version 3 of the
#   License, or (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU Affero General Public License for more details.
#
#   You should have received a copy of the GNU Affero General Public License
#   along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
###############################################################################

class Openlabs_OpenERPConnector_Model_Oerpcatalog_Attribute_Api extends Mage_Catalog_Model_Product_Attribute_Api
{

    // start copy paste from magento API
    public function addOption($attribute, $data)
    {
        $model = $this->_getAttribute($attribute);

        if (!$model->usesSource()) {
            $this->_fault('invalid_frontend_input');
        }
        /** @var $helperCatalog Mage_Catalog_Helper_Data */
        $helperCatalog = Mage::helper('catalog');

        $optionLabels = array();
        foreach ($data['label'] as $label) {
            $storeId = $label['store_id'];
            $labelText = $helperCatalog->stripTags($label['value']);
            if (is_array($storeId)) {
                foreach ($storeId as $multiStoreId) {
                    $optionLabels[$multiStoreId] = $labelText;
                }
            } else {
                $optionLabels[$storeId] = $labelText;
            }
        }

        // data in the following format is accepted by the model
        // it simulates parameters of the request made to
        // Mage_Adminhtml_Catalog_Product_AttributeController::saveAction()
        $modelData = array(
            'option' => array(
                'value' => array(
                    'option_1' => $optionLabels
                ),
                'order' => array(
                    'option_1' => (int) $data['order']
                )
            )
        );
        if ($data['is_default']) {
            $modelData['default'][] = 'option_1';
        }

        $model->addData($modelData);
        try {
            $model->save();
        } catch (Exception $e) {
            $this->_fault('unable_to_add_option', $e->getMessage());
        }
        // end of copy paste from magento API


        /* TODO FIXME
        Ugly code but working code
        that search for the id of the value created
        I assume that the greatest id is the id of the
        value created
        Sorry I am not a Magento Dev, If you have a better
        solution, please please please contribute it!
        Thanks
        */ 
        $option_id = '';
        foreach ($model->getSource()->getAllOptions() as $optionId => $optionValue) {
            $id = $optionValue['value'];
            if ($id > $option_id) {
                $option_id = $id;
            }
        }

        return $option_id;

    }

    /* code inspired from http://www.webspeaks.in/2012/05/addupdate-attribute-option-values.html
        copyright 2012 Arvind Bhardwaj <bhardwajs.on.height@gmail.com>
    */
    public function updateOption($attribute_id, $option_id, $data)
    {

        $model = $this->_getAttribute($attribute_id);

        if (!$model->usesSource()) {
            $this->_fault('invalid_frontend_input');
        }
        /** @var $helperCatalog Mage_Catalog_Helper_Data */
        $helperCatalog = Mage::helper('catalog');

        $optionLabels = array();
        foreach ($data['label'] as $label) {
            $storeId = $label['store_id'];
            $labelText = $helperCatalog->stripTags($label['value']);
            if (is_array($storeId)) {
                foreach ($storeId as $multiStoreId) {
                    $optionLabels[$multiStoreId] = $labelText;
                }
            } else {
                $optionLabels[$storeId] = $labelText;
            }
        }

        // data in the following format is accepted by the model
        // it simulates parameters of the request made to
        // Mage_Adminhtml_Catalog_Product_AttributeController::saveAction()
        $modelData = array(
            'option' => array(
                'value' => array(
                    $option_id => $optionLabels
                ),
                'order' => array(
                    $option_id => (int) $data['order']
                )
            )
        );

        if ($data['is_default']) {
            $modelData['default'][] = $option_id;
        }
        
        //Add data to our attribute model
        $model->addData($modelData);

        //Save the updated model
        $model->save();
        $session = Mage::getSingleton('adminhtml/session');
        $session->addSuccess(
            Mage::helper('catalog')->__('The product attribute has been saved.'));

        /**
         * Clear translation cache because attribute labels are stored in translation
         */
        Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
        $session->setAttributeData(false);
        return True;
    }
}
