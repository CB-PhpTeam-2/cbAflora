<?php

namespace Cb\RecommendationTool\Controller\Adminhtml\Locations;

class Delete extends \Magento\Backend\App\Action
{
	/**
	 * Delete action
	 *
	 * @return \Magento\Backend\Model\View\Result\Redirect
	 */
	public function execute()
	{	
		// check if we know what should be deleted
		$id = $this->getRequest()->getParam('id');
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		if ($id) {
			try {
				// init model and delete
				$model = $this->_objectManager->create('Cb\RecommendationTool\Model\Locations');
				$model->load($id);
				$model->delete();

				// display success message
				$this->messageManager->addSuccess(__('You deleted the Recommendation Tool.'));
				// go to grid
				return $resultRedirect->setPath('*/*/');
			}
			catch (\Exception $e)
			{
				// display error message
				$this->messageManager->addError($e->getMessage());
				// go back to edit form
				return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
			}
		}
		// display error message
		$this->messageManager->addError(__('We can\'t find a legendtool to delete.'));
		// go to grid
		return $resultRedirect->setPath('*/*/');
	}
}