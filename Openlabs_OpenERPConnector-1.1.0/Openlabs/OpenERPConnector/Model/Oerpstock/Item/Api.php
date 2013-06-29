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

/* Inspired from http://www.sonassi.com/knowledge-base/magento-kb/mass-update-stock-levels-in-magento-fast/
*/

class Openlabs_OpenERPConnector_Model_Oerpstock_item_api extends Mage_CatalogInventory_Model_Stock_Item_Api
{

    protected function _getProduct($productId, $store = null, $identifierType = 'id')
    {
        $product = Mage::helper('catalog/product')->getProduct($productId, $this->_getStoreId($store), $identifierType);
        if (is_null($product->getId())) {
            $this->_fault('product_not_exists');
        }
        return $product;
    }

    protected function _updateStock($productId, $data)
    {
        // test if product exist
        $product = $this->_getProduct($productId);

        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        $stockItemId = $stockItem->getId();

        if (!$stockItemId) {
             $stockItem->setData('product_id', $productId);
             $stockItem->setData('stock_id', 1); 
        } else {
             $stock = $stockItem->getData();
        }

        foreach($data as $field=>$value) {
            $stockItem->setData($field, $value?$value:0);
        }

        $stockItem->save();
        return true;
    }

    public function update($productId, $data)
    {
        return $this->_updateStock($productId, $data);
    }

    public function massive_update($datas)
    {
        foreach($datas as $productId=>$data) {
            $this->_updateStock($productId, $data);
        }
    return true;
    }

} // Class Mage_CatalogInventory_Model_Stock_Item_Api End
