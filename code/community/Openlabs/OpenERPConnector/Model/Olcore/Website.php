<?php

/**
 * @author Sharoon Thomas
 * Inspired from Dieter's Magento Extender
 * @copyright 2009
 */

class Openlabs_OpenERPConnector_Model_Olcore_Website extends Mage_Catalog_Model_Api_Resource
{

	public function items()
	{
		$websites = array();
		foreach(Mage::app()->getWebsites(true) as $websiteId => $website)
                    {
        		$websites[] = $this->_websiteToArray($website);
                    }
			
			return $websites;
	}

        public function info($websiteIds = null)
	{
            if(is_integer($websiteIds))
		{
			try
                        {
                            return $this->_websiteToArray(Mage::app()->getWebsite($websiteIds));
			}
                        catch (Mage_Core_Exception $e)
                        {
                            $this->_fault('website_not_exists',$e->getMessage());
                        }
                        catch (Exception $e)
                        {
                            $this->_fault('website_not_exists',$e->getMessage());
                        }

          }
          elseif(is_array($websiteIds))
          {
                $websites = array();
                foreach ($websiteIds as $websiteid)
                {
                    try
                        {
				$websites[] = $this->_websiteToArray(Mage::app()->getWebsite($websiteid));
			}
                        catch (Mage_Core_Exception $e)
                        {
                            $this->_fault('website_not_exists',$e->getMessage());
                        }
                        catch (Exception $e)
                        {
                            $this->_fault('website_not_exists',$e->getMessage());
                        }
                }
                return $websites;
          }
	}
	//This is a protected function used by items & info for fetching website information
	protected function _websiteToArray($website)
	{
		return array(                   'website_id' 		=> $website->getWebsiteId(),
						'code'			=> $website->getCode(),		
						'name'			=> $website->getName(),		
						'sort_order'		=> $website->getSortOrder(),
						'default_group_id'	=> $website->getDefaultGroupId(),
						'is_default'		=> $website->getIsDefault(),
						'group_ids'		=> $website->getGroupIds()
						);
	}
	
	
	public function create($websitedata)
        {
            try
            {
                $website = Mage::getModel('core/website')
                    ->setData($websitedata)
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
            return $website->getId();
        }

        public function update($websiteid,$websitedata)
        {
            try
            {
                $website = Mage::getModel('core/website')
                    ->load($websiteid);
                if (!$website->getId())
                {
                    $this->_fault('website_not_exists');
                }
                $website->addData($websitedata)->save();
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

        public function delete($websiteid)
        {
            try
            {
                $website = Mage::getModel('core/website')
                    ->load($websiteid);
                if (!$website->getId())
                {
                    $this->_fault('website_not_exists');
                }
                $website->delete();
                
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
        
	public function tree()
	{
		$tree = array();
		
		$websites = $this->websites();
		
		foreach($websites as $website)
		{
			$groups = $this->groups($website['group_ids']);	
			$tree[$website['code']] = $website;
			foreach($groups as $group)
			{
				$stores = $this->stores($group["store_ids"]);
				
				$tree[$website['code']]['groups']['group_'.$group['group_id']] = $group;
				
				foreach($stores as $store)
				{
					$tree[$website['code']]['groups']['group_'.$group['group_id']]['stores'][$store['code']] = $store;
				}
			}
		}

		return $tree;
	}
	
}
?>