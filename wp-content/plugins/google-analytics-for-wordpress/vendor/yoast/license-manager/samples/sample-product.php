<?php

/**
 * Class Sample_Product
 *
 * Our sample product class
 */
class Sample_Product extends MI_Product {

	public function __construct() {
		parent::__construct(
				'https://www.monsterinsights.com',
				'Sample Product',
				'sample-product',
				'1.0',
				'https://www.monsterinsights.com/downloads/sample-product/',
				'admin.php?page=sample-product',
				'sample-product',
				'MonsterInsights'
		);
	}

}