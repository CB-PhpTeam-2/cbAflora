<?php

namespace Cb\RecommendationTool\Controller\Adminhtml\Locations;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Cb\RecommendationTool\Controller\Adminhtml\Locations
{
	protected $resultPageFactory;

	public function __construct(
		Context $context,
		Registry $coreRegistry,
		PageFactory $resultPageFactory
	)
	{
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context, $coreRegistry);
	}

	public function execute()
	{	
		$resultPage = $this->resultPageFactory->create();
		$this->_initAction($resultPage)->getConfig()->getTitle()->prepend(__('Recommendation Tool Manager'));
		return $resultPage;
	}
}