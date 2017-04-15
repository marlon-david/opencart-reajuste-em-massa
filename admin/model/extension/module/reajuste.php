<?php
/**
 * @since 09/01/2017
 */
class ModelExtensionModuleReajuste extends Model {
	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT pd.name, p.price FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function updatePrice($price, $category_id) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) SET p.price = '" . (float)$price . "' WHERE p2c.category_id = '" . (int)$category_id . "'");
	}

	public function updatePercent($percent, $category_id) {
		if ($percent < 0) {
			$percent = abs($percent) / 100;
			$query = $this->db->query("UPDATE " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) SET p.price = (p.price - (p.price * " . $percent . ")) WHERE p2c.category_id = '" . (int)$category_id . "'");
		} else {
			$percent = (float)$percent / 100;
			$query = $this->db->query("UPDATE " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) SET p.price = (p.price + (p.price * " . $percent . ")) WHERE p2c.category_id = '" . (int)$category_id . "'");
		}
	}
}
