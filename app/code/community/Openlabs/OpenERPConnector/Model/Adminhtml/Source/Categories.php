<?php
/**
 * @author Daniel Lozano Morales <daniel.lozano@juguetronica.com>
 */
class Openlabs_OpenERPConnector_Model_Adminhtml_Source_Categories
{
	/**
	 * Colección de categorías
	 * 
	 * @var Mage_Catalog_Model_Category_Collection
	 */
	private $collection;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		/**
		 * Obtener el store id desde la parte de administración. No hay
		 * ninguna store cargada por lo que hay que buscar en la 
		 * configuración
		 */
		if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) {
		    $store_id = Mage::getModel('core/store')->load($code)->getId();
		} elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) {
		    $website_id = Mage::getModel('core/website')->load($code)->getId();
		    $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
		} else {
		    $store_id = 0;
		}

		$store = Mage::getModel('core/store')->load($store_id);
		$rootId = $store->getRootCategoryId();
		$category = Mage::getModel('catalog/category')->load($rootId);
		/**
		 * Obtener colección de categorias
		 */
		$collection = $category->getCollection()
			->addAttributeToSelect(array('name', 'entity_id' , 'is_active'))
			->addFieldToFilter('is_active', array('in' => array(0, 1, 2, 3, 4)));

		/** @var Mage_Catalog_Model_Category_Collection */
		$this->collection = $collection;
	}

	/**
	 * Devolver array de opciones para el selector
	 * múltiple
	 * 
	 * @return array
	 */
	public function toOptionArray()
	{	
		// Valor para vaciar las categorías.
		$array = array(
			array('value' => null, 'label' => 'None')
		);
		// TODO: optimizar, eso se ejecuta cada vez que se carga esta opción....
		if (count($this->collection) > 0) {
			foreach ($this->collection as $category) {
				$array[] = array(
					'value' 	=> $category->getId(),
					'label' 	=> $category->getName() . '(' . $category->getId() . ')'
				);
			}
		}
		return $array;
	}
}