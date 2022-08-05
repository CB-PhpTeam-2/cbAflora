<?php

namespace Cb\RecommendationTool\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class Locations extends AbstractDb
{
	public function __construct(
		Context $context,
		StoreManagerInterface $storeManager,
		$connectionName = null
	)
	{
		parent::__construct($context, $connectionName);
		$this->_storeManager = $storeManager;
	}

	public function _construct()
	{
		$this->_init('recommendation_mapping_values', 'id');
	}
}