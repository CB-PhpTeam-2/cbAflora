<?php

namespace Cb\RecommendationTool\Block\Adminhtml;

class Locations extends \Magento\Backend\Block\Widget\Grid\Container
{

	protected function _construct()
	{	
		$this->_blockGroup = 'Cb_RecommendationTool';
		$this->_controller = 'adminhtml_locations';
		$this->_headerText = __('Recommendation Tool Manager');
		parent::_construct();

		if ($this->_isAllowedAction('Cb_RecommendationTool::save')) {
			$this->buttonList->update('add', 'label', __('Add New Recommendation Tool'));
		} else {
			$this->buttonList->remove('add');
		}
	}

	protected function _isAllowedAction($resourceId)
	{
		return $this->_authorization->isAllowed($resourceId);
	}
}