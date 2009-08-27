<?php


/**
 * @author Sharoon Thomas
 * Inspired from Dieter's Magento Extender
 * @copyright 2009
 */

class Openlabs_OpenERPConnector_Model_Olcatalog_Categories extends Mage_Catalog_Model_Api_Resource {
	public function items($filters = null) {
		try {
			$collection = Mage :: getModel('catalog/category/attribute')->getCollection()->addAttributeToSelect('image');
		} catch (Mage_Core_Exception $e) {
			$this->_fault('category_not_exists');
		}
		if (is_array($filters)) {
			try {
				foreach ($filters as $field => $value) {
					$collection->addFieldToFilter($field, $value);
				}
			} catch (Mage_Core_Exception $e) {
				$this->_fault('filters_invalid', $e->getMessage());
				// If we are adding filter on non-existent attribute
			}
		}

		$result = array ();
		foreach ($collection as $category) {
			//$result[] = $customer->toArray();
			$result[] = array (
				'category_id' => $category->getId(),
				'image' => $category->getImage(),

				
			);
		}

		return $result;
	}

	public function info($categoryId = null) {
		if (is_numeric($categoryId)) {
			try {
				$collection = Mage :: getModel('catalog/category/attribute')->load($categoryId)->getCollection()->addAttributeToSelect('image');
				$result = array ();
				foreach ($collection as $category) {
					//$result[] = $customer->toArray();
					if ($category->getId() == $categoryId) {
						$image = $category->getImage();
						if ($image) {
							$path = Mage :: getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
							$fullpath = $path . $image;
							try {
								$fp = fopen($fullpath, "rb");
								$imagebin = fread($fp, filesize($fullpath));
								$img_data = base64_encode($imagebin);
							} catch (Exception $e) {
								return "exc proc";
							}
						}
						$result[] = array (
							'category_id' => $category->getId(),
							'image' => $category->getImage(),
							'image_data' => $img_data
						);
					}
				}

				return $result;

			} catch (Mage_Core_Exception $e) {
				$this->_fault('group_not_exists');
			}

		}

	}

	public function create($groupdata) {
		try {
			$group = Mage :: getModel('core/store_group')->setData($groupdata)->save();

		} catch (Magento_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
		return $group->getId();
	}

	public function update($groupid, $groupdata) {
		try {
			$group = Mage :: getModel('core/store_group')->load($groupid);
			if (!$group->getId()) {
				$this->_fault('group_not_exists');
			}
			$group->addData($groupdata)->save();
		} catch (Magento_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
		return true;
	}

	public function delete($groupid) {
		try {
			$group = Mage :: getModel('core/store_group')->load($groupid);
			if (!$group->getId()) {
				$this->_fault('group_not_exists');
			}
			$group->delete();

		} catch (Magento_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		} catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
		return true;
	}

	protected function _getResource() {
		return Mage :: getResourceSingleton('catalog/category_attribute_backend_media');
	}
}
?>
