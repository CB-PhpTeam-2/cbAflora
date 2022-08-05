<?php
/**
 * @category   Webkul
 * @package    Webkul_AdvancedLayeredNavigation
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Controller\Adminhtml\Attributes;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class DisplayType extends \Magento\Backend\App\Action
{
    /**
     * @var Attribute $attributeFactory
     */
    private $attributeFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Attribute $attributeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Attribute $attributeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->filter = $filter;
        $this->attributeFactory = $attributeFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $isFilterable = $params['group'];
        foreach ($params['advancedlayerednavigation'] as $attributeId) {
            $attributeInfo = $this->attributeFactory->load($attributeId);
            if ($attributeInfo->getFrontendInput() == 'price') {
                $attributeInfo->setIsDisplay(0)->save();
                continue;
            }
            $attributeInfo->setIsDisplay($isFilterable);
            $attributeInfo->save();
        }
        $this->messageManager->addSuccess(__('View type updated successfully'));
        $this->_redirect('catalog/product_attribute/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_AdvancedLayeredNavigation::assign');
    }
}
