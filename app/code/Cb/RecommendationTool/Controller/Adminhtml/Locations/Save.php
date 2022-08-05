<?php

namespace Cb\RecommendationTool\Controller\Adminhtml\Locations;

use Magento\Backend\App\Action;

class Save extends Action
{
	/**
	 * Save action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */

	public function createLocations()
	{
		return $this->_objectManager->create('Cb\RecommendationTool\Model\Locations');
	}

	public function execute()
	{	
		$data = $this->getRequest()->getPostValue();
		//echo "<pre>"; print_r($data);die;
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		if ($data) {
			$id = $this->getRequest()->getParam('id');
			$locations = $this->createLocations();

			$model = $locations->load($id);
			if (!$model->getId() && $id) {
				$this->messageManager->addError(__('This locations no longer exists.'));
				return $resultRedirect->setPath('*/*/');
			}

			$collection = $locations->getCollection()
					->addFieldToFilter('frontend_label',$data['frontend_label'])
					->addFieldToFilter('backend_label',$data['backend_label'])
					->addFieldToFilter('attribute_code',$data['attribute_code']);

			if($collection->getSize() > 0){
				$coll = $collection->getFirstItem();
				if($model->getId() && $coll->getId() == $model->getId()){
					
				}else{
					$this->messageManager->addError(__('This legendtool already exists.'));
					return $resultRedirect->setPath('*/*/');
				}
			}			

			// init model and set data
			$model->setData($data);

			// try to save it
			try {
				// save the data
				$model->save();
				
				// display success message
				$this->messageManager->addSuccess(__('You saved the Recommendation Tool.'));
				// clear previously saved data from session
				$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
					return $resultRedirect->setPath('*/*/edit', [
						'id' => $model->getId(),
						'activeTab' => $this->getRequest()->getParam('activeTab')
					]);
				}
				// go to grid
				return $resultRedirect->setPath('*/*/');
			}
			catch (\Exception $e)
			{
				// display error message
				$this->messageManager->addError($e->getMessage());
				// save data in session
				$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
				// redirect to edit form
				return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('gid')]);
			}
		}
		return $resultRedirect->setPath('*/*/');
	}
}