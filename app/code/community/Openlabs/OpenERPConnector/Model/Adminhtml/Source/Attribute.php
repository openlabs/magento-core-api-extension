<?php
/**
 * @author  Daniel Lozano Morales <daniel.lozano@juguetronica.com>
 */
class Openlabs_OpenERPConnector_Model_Adminhtml_Source_Attribute
{
	/**
	 * Colección de atributos
	 * 
	 * @var Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	private $collection;

	public function __construct()
	{
		$this->collection = Mage::getResourceModel('catalog/product_attribute_collection');
	}

	/**
	 * Devolver array con opciones para el multiselect
	 * 
	 * @return array
	 */
	public function toOptionArray()
	{
		$result = array();

		// Ordenar colección alfabéticamente.
		$attributes = $this->collection->setOrder('frontend_label', 'ASC')->getItems();

		if (count($attributes) > 0) {
			foreach ($attributes as $attribute) {
				$value = $attribute->getData('attribute_code');
				$label = $attribute->getData('frontend_label');

				if ($label !== null && $value !== null) {
					$result[] = array(
						'value' => $value,
						'label' => $label
					);
				}
			}
		}
		return $result;
	}
}