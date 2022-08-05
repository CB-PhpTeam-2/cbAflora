<?php

namespace Cb\RecommendationTool\Model;

use Cb\RecommendationTool\Api\Data\LocationsInterface;
use Magento\Framework\Model\AbstractModel;

class Locations extends AbstractModel implements LocationsInterface
{
	protected function _construct()
	{
		$this->_init('Cb\RecommendationTool\Model\ResourceModel\Locations');
	}

	public function getId()
	{
		return $this->getData(self::ID);
	}

	public function getFrontendLabel()
	{
		return $this->getData(self::FRONTEND_LABEL);
	}

	public function getBackendLabel()
	{
		return $this->getData(self::BACKEND_LABEL);
	}


	public function setId($id)
	{
		return $this->setData(self::ID, $id);
	}

	public function setFrontendLabel($frontendLabel)
	{
		return $this->setData(self::FRONTEND_LABEL, $frontendLabel);
	}

	public function setBackendLabel($backendLabel)
	{
		return $this->setData(self::BACKEND_LABEL, $backendLabel);
	}
	
}