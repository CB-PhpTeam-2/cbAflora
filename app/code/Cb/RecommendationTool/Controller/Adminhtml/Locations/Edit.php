<?php

namespace Cb\RecommendationTool\Controller\Adminhtml\Locations;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
	protected $resultPageFactory;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	) {
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
	}

	protected function _initAction()
	{
		// load layout, set active menu and breadcrumbs
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Cb_RecommendationTool::postcode_locations')
			->addBreadcrumb(__('Recommendation Tool Manager'), __('Recommendation Tool Manager'))
			->addBreadcrumb(__('Recommendation Tool Manager'), __('Recommendation Tool Manager'));
		return $resultPage;
	}

	public function execute()
	{	
		// 1. Get ID and create model
		$id = $this->getRequest()->getParam('id');
		$model = $this->_objectManager->create('Cb\RecommendationTool\Model\Locations');

		// 2. Initial checking
		if ($id) {
			$model->load($id);
			if (!$model->getId()) {
				$this->messageManager->addError(__('This Recommendation Tool no longer exists.'));
				/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
				$resultRedirect = $this->resultRedirectFactory->create();

				return $resultRedirect->setPath('*/*/');
			}
		}
		if ($model->getId() || $id == 0) {
			// 3. Set entered data if was error when we do save
			$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			
			// 4. Register model to use later in blocks
			$_coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
			$_coreRegistry->register('postcode_locations', $model);

			// 5. Build edit form
			/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
			$resultPage = $this->_initAction();
			$resultPage->addBreadcrumb(
				$id ? __('Edit Recommendation Tool') : __('New Recommendation Tool'),
				$id ? __('Edit Recommendation Tool') : __('New Recommendation Tool')
			);
			$resultPage->addContent(
				$this->_view->getLayout()->createBlock('\Cb\RecommendationTool\Block\Adminhtml\Locations\Edit')
			);
			$resultPage->addLeft(
				$this->_view->getLayout()->createBlock('\Cb\RecommendationTool\Block\Adminhtml\Locations\Edit\Tabs')
			);

			$resultPage->getConfig()->getTitle()->prepend(__('Locations'));
			$resultPage->getConfig()->getTitle()
				->prepend($model->getId() ? $model->getTitle() : __('New Recommendation Tool'));
			return $resultPage;
		}
	}
}