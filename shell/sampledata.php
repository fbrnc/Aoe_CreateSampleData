<?php

require_once 'abstract.php';

class Aoe_CreateSampleData_Shell_SampleData extends Mage_Shell_Abstract {

	/**
	 * Run script
	 * 
	 * @return void
	 */
	public function run() {
		$action = $this->getArg('action');
		if (empty($action)) {
			echo $this->usageHelp();
		} else {
			$actionMethodName = $action.'Action';
			if (method_exists($this, $actionMethodName)) {
				$this->$actionMethodName();
			} else {
				echo "Action $action not found!\n";
				echo $this->usageHelp();
				exit(1);
			}
		}
	}



	/**
	 * Retrieve Usage Help Message
	 *
	 * @return string
	 */
	public function usageHelp() {
		$help = 'Available actions: ' . "\n";
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (substr($method, -6) == 'Action') {
				$help .= '    -action ' . substr($method, 0, -6);
				$helpMethod = $method.'Help';
				if (method_exists($this, $helpMethod)) {
					$help .= $this->$helpMethod();
				}
				$help .= "\n";
			}
		}
		return $help;
	}



	/**
	 * List all availables codes / jobs
	 *
	 * @return void
	 */
	public function createStoresAction() {

		$numberOfWebsites = 50;
		$numberOfStoreGroupsPerWebsite = 2;
		$numberOfStoresPerStoreGroup = 2;

		for ($websiteNumber = 0; $websiteNumber < $numberOfWebsites; $websiteNumber++) {
			$website = $this->createWebsite(
				'ws_'.$websiteNumber,
				'Website #'.$websiteNumber
			);
			for ($storeGroupNumber = 0; $storeGroupNumber < $numberOfStoreGroupsPerWebsite; $storeGroupNumber++) {
				$storeGroup = $this->createStoreGroupForWebsite(
					$website,
					'Store group #'.$websiteNumber.'.'.$storeGroupNumber
				);
				for ($storeNumber = 0; $storeNumber < $numberOfStoresPerStoreGroup; $storeNumber++) {
					$store = $this->createStoreForStoreGroup(
						$storeGroup,
						'Store #'.$websiteNumber.'.'.$storeGroupNumber.'.'.$storeNumber,
						'store_'.$websiteNumber.'_'.$storeGroupNumber.'_'.$storeNumber
					);
				}
			}
		}

	}

	/**
	 * @param $websiteCode
	 * @param $websiteName
	 * @return Mage_Core_Model_Website
	 */
	public function createWebsite($websiteCode, $websiteName) {
		echo "Creating website: $websiteCode, $websiteName\n";
		$website = Mage::getModel('core/website'); /* @var $website Mage_Core_Model_Website */
		$website->load($websiteCode); // try loading existing store first
		$website->setCode($websiteCode)
			->setName($websiteName)
			->save();
		return $website;
	}

	/**
	 * @param Mage_Core_Model_Website $website
	 * @param $storeGroupName
	 * @param int $categoryRootId
	 * @return Mage_Core_Model_Store_Group
	 */
	public function createStoreGroupForWebsite(Mage_Core_Model_Website $website, $storeGroupName, $categoryRootId=3) {
		echo "  Creating store group: $storeGroupName\n";
		$storeGroup = Mage::getModel('core/store_group'); /* @var $storeGroup Mage_Core_Model_Store_Group */
		$storeGroup->setWebsiteId($website->getId())
			->setName($storeGroupName)
			->setRootCategoryId($categoryRootId)
			->save();
		return $storeGroup;
	}

	/**
	 * Create store
	 *
	 * @param Mage_Core_Model_Store_Group $storeGroup
	 * @param $storeName
	 * @param $storeCode
	 * @return Mage_Core_Model_Store
	 */
	public function createStoreForStoreGroup(Mage_Core_Model_Store_Group $storeGroup, $storeName, $storeCode) {
		echo "    Creating store: $storeName, $storeCode\n";
		$store = Mage::getModel('core/store'); /* @var $store Mage_Core_Model_Store */
		$store->setCode($storeCode)
			->setWebsiteId($storeGroup->getWebsiteId())
			->setGroupId($storeGroup->getId())
			->setName($storeName)
			->setIsActive(1)
			->save();
		return $store;
	}


}

$shell = new Aoe_CreateSampleData_Shell_SampleData();
$shell->run();