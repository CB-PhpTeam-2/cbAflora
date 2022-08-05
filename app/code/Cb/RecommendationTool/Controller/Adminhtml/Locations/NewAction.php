<?php

namespace Cb\RecommendationTool\Controller\Adminhtml\Locations;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;

//class NewAction extends \Cb\RecommendationTool\Controller\Adminhtml\Locations
class NewAction extends \Magento\Backend\App\Action
{
	protected $resultForwardFactory;

	public function __construct(
		Context $context,
		ForwardFactory $resultForwardFactory
	) {
		$this->resultForwardFactory = $resultForwardFactory;
		parent::__construct($context);
	}

	public function execute()
	{
		/** @var \Magento\Framework\Controller\Result\Forward $resultForward */
		$resultForward = $this->resultForwardFactory->create();
		return $resultForward->forward('edit');
	}
}