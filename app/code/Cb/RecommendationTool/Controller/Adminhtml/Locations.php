<?php

namespace Cb\RecommendationTool\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Locations extends \Magento\Backend\App\Action
{
	protected $_coreRegistry = null;

	public function __construct(
		Context $context,
		Registry $registry
	)
	{
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}

	/**
	 * Init page
	 *
	 * @param \Magento\Backend\Model\View\Result\Page $resultPage
	 * @return \Magento\Backend\Model\View\Result\Page
	 */
	protected function _initAction($resultPage)
	{
		$resultPage->setActiveMenu('Cb_RecommendationTool::postcode_locations');
		$resultPage->addBreadcrumb(__('Recommendation Tool Manager'), __('Recommendation Tool Manager'))
			->addBreadcrumb(__('Recommendation Tool'), __('Recommendation Tool'));
		return $resultPage;
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Cb_RecommendationTool::save');
	}
}