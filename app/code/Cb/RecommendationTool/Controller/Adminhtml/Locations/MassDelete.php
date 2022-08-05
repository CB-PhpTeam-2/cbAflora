<?php

namespace Cb\RecommendationTool\Controller\Adminhtml\Locations;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class MassDelete extends \Magento\Backend\App\Action
{
	protected $_coreRegistry;

	protected $query;

	public function __construct(
		Context $context,
		Registry $registry
	)
	{
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}

	public function execute(){
		
		$locationIds = $this->getRequest()->getParam('locations_param');
		$model = $this->_objectManager->create('Cb\RecommendationTool\Model\Locations');
		$count = 0;

		if(!is_array($locationIds)) {
			$this->messageManager->addError(__('Please select item(s)'));
		}
		else{
			try {
				foreach ($locationIds as $locationId) {
					$collection = $model->load($locationId);
					$collection->delete();
					$count++;
				}

				$this->messageManager->addSuccess(
					__('A total of %1 record(s) have been deleted.', $count)
				);
				$redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
				return $redirect->setPath('postcode/locations/index');
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->_getSession()->addException($e, __('Something went wrong while updating the legendtool(s) status.'));
			}
		}
	}
}