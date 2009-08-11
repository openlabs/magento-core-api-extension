<?php

/**
 * @author Sharoon Thomas
 * Inspired from Dieter's Magento Extender
 * @copyright 2009
 */

class Openlabs_OpenERPConnector_Model_Olcore_Storeviews extends Mage_Catalog_Model_Api_Resource
{
        public function items()
	{
                $stores = array();
                try
                {
                    foreach(Mage::app()->getStores(true) as $storeId => $store)
			{
				$stores[] = $this->_storeToArray($store);
			}
                }
                catch (Mage_Core_Exception $e)
                {
                    $this->_fault('store_not_exists');
                }
                return $stores;
        }
	public function info($storeIds = null)
	{
		$stores = array();

		if(is_array($storeIds))
		{
			foreach($storeIds as $storeId)
			{
				try
                                {
                                    $stores[] = $this->_storeToArray(Mage::app()->getStore($storeId));
				}
                                catch (Mage_Core_Exception $e)
                                {
                                    $this->_fault('store_not_exists');
                                }
                        }
                        return $stores;
		}
                elseif(is_numeric($storeIds))
		{
			try
                        {
                            return $this->_storeToArray(Mage::app()->getStore((int)$storeIds));
			}
                        catch (Mage_Core_Exception $e)
                        {
                            $this->_fault('store_not_exists');
                        }

                }
		
        }

	protected function _storeToArray($store)
	{
		return array(	'store_id'                  => $store->getStoreId(),
                                'code'                      => $store->getCode(),
                                'website_id'                => $store->getWebsiteId(),
                                'group_id'                  => $store->getGroupId(),
                                'name'                      => $store->getName(),
                                'sort_order'                => $store->getSortOrder(),
                                'is_active'                 => $store->getIsActive(),
                                'is_admin'                  => $store->isAdmin(),
                                'is_can_delete'             => $store->isCanDelete(),
                                'url'                       => $store->getUrl(),
                                'is_currently_secure'       => $store->isCurrentlySecure(),
                                'current_currency_code'     => $store->getCurrentCurrencyCode(),
                                'root_category_id'          => $store->getRootCategoryId()
						);
	}

        public function create($storeedata)
        {
            try
            {
                $store = Mage::getModel('core/store')
                    ->setData($storedata)
                    ->save();

            }
            catch (Magento_Core_Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            catch (Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            return $store->getId();
        }

        public function update($storeid,$storedata)
        {
            try
            {
                $store = Mage::getModel('core/store')
                    ->load($storeid);
                if (!$store->getId())
                {
                    $this->_fault('store_not_exists');
                }
                $store->addData($storedata)->save();
            }
            catch (Magento_Core_Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            catch (Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            return true;
        }

        public function delete($storeid)
        {
            try
            {
                $store = Mage::getModel('core/store')
                    ->load($storeid);
                if (!$store->getId())
                {
                    $this->_fault('store_not_exists');
                }
                $store->delete();

            }
            catch (Magento_Core_Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            catch (Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            return true;
        }
}
?>
