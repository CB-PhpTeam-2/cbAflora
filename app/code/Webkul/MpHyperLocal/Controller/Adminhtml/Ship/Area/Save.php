<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Controller\Adminhtml\Ship\Area;

use Magento\Framework\Locale\Resolver;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param ShipAreaFactory $shipArea
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ShipAreaFactory $shipArea
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->shipArea = $shipArea;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('*/*/index');
            return;
        }
        $model = $this->shipArea->create()->load($id);

        if ($id && $model->isObjectNew()) {
            $this->messageManager->addError(__('This ship area no longer exists.'));
            $this->_redirect('*/*/index');
            return;
        }

        try {
            $model->setData($data);
            $model->save();
            $this->messageManager->addSuccess(__('You saved the ship area detail.'));
            $this->_redirect('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addMessages($e->getMessages());
            $this->redirectToEdit($data);
        }
    }

    /**
     * check permission
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpHyperLocal::shiparea_add');
    }

    /**
     * @param array $data
     * @return void
     */
    private function redirectToEdit(array $data)
    {
        $data['entity_id'] = isset($data['entity_id']) ? $data['entity_id']:0;
        $arguments = $data['entity_id'] ? ['id' => $data['entity_id']]: [];
        $arguments = array_merge(
            $arguments,
            ['_current' => true, 'active_tab' => '']
        );
        if ($data['entity_id']) {
            $this->_redirect('*/*/edit', $arguments);
        } else {
            $this->_redirect('*/*/index', $arguments);
        }
    }
}
