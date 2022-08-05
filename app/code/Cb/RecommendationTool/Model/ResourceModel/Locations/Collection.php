<?php

namespace Cb\RecommendationTool\Model\ResourceModel\Locations;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	protected function _construct()
	{
		$this->_init('Cb\RecommendationTool\Model\Locations', 'Cb\RecommendationTool\Model\ResourceModel\Locations');
	}
}