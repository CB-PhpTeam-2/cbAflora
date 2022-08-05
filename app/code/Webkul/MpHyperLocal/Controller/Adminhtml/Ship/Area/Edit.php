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
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $shipArea;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param ShipAreaFactory $shipArea,
     * @param Registry $registry
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ShipAreaFactory $shipArea,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->shipArea = $shipArea;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Init actions
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpHyperLocal::manager')
            ->addBreadcrumb(__('Lists'), __('Lists'))
            ->addBreadcrumb(__('Manage Info'), __('Manage Info'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $shipArea=$this->shipArea->create();
        if ($id) {
            $shipArea->load($id);
            if (!$shipArea->getEntityId()) {
                $this->messageManager->addError(__('This ship area no longer exists.'));
                $this->_redirect('*/*/index');
                return;
            }
        }

        $this->coreRegistry->register('ship_area', $shipArea);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Ship Area') : __('New Ship Area'),
            $id ? __('Edit info') : __('New Info')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ?__('Edit Ship Area') : __('New Ship Area'));
        return $resultPage;
    }

    /**
     * check permission
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpHyperLocal::shiparea_list');
    }
}
