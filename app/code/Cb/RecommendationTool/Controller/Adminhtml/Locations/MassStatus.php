<?php

namespace Cb\RecommendationTool\Controller\Adminhtml\Locations;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class MassStatus extends \Magento\Backend\App\Action
{
	protected $_coreRegistry;

	public function __construct(
		Context $context,
		Registry $registry
	)
	{
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}

	/**
	 * Update product(s) status action
	 *
	 * @return \Magento\Backend\Model\View\Result\Redirect
	 */
	public function execute(){
		$locationIds = $this->getRequest()->getParam('locations_param');
		$model = $this->_objectManager->create('Cb\RecommendationTool\Model\Locations');
		$count = 0;
		if(!is_array($locationIds)) {
			$this->messageManager->addError($this->__('Please select item(s)'));
		}
		else{
			try {
				foreach ($locationIds as $locationId) {
					$model->load($locationId)
						->setStatus($this->getRequest()->getParam('status'))
						->setIsMassupdate(true)
						->save();
					$count++;
				}
				$this->messageManager->addSuccess(
					__('A total of %1 record(s) have been update status.', $count)
				);
				$redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
				return $redirect->setPath('postcode/locations/index');
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->_getSession()->addException($e, __('Something went wrong while updating the location(s) status.'));
			}
		}
		$redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		return $redirect->setPath('postcode/locations/index');
	}
}