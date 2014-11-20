<?php

namespace SS6\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SS6\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\TopProduct\TopProduct;
use SS6\ShopBundle\Model\Product\TopProduct\TopProductData;

class TopProductsDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface {

	private $productsData = array(
		// $productReferenceName => $domainId
		'product_7' => 1,
		'product_17' => 1,
		'product_9' => 1,
		'product_14' => 2,
		'product_10' => 2,
		'product_1' => 2,
	);

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 */
	public function load(ObjectManager $manager) {
		foreach ($this->productsData as $productReferenceName => $domainId) {
			$product = $this->getReference($productReferenceName);
			$this->createTopProduct($manager, $product, $domainId);
		}

		$manager->flush();
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param int $domainId
	 */
	private function createTopProduct(ObjectManager $manager, Product $product, $domainId) {
		$topProductData = new TopProductData();
		$topProductData->setProduct($product);

		$topProduct = new TopProduct($domainId, $topProductData);

		$manager->persist($topProduct);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDependencies() {
		return array(
			ProductDataFixture::class,
		);
	}
}
