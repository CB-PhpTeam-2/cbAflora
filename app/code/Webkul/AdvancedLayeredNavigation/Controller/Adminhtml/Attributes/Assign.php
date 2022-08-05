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

class Assign extends \Magento\Backend\App\Action
{
    /**
     * @var Attribute $attributeFactory
     */
    private $attributeFactory;

    /**
     * @param \Magento\Backend\App\Action\Context        $context
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Attribute $attributeFactory
    ) {
        $this->filter = $filter;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $isFilterable = $params['group'] - 1;
        foreach ($params['advancedlayerednavigation'] as $attributeId) {
            $attributeInfo = $this->attributeFactory->load($attributeId);
            $attributeInfo->setIsFilterable($isFilterable);
            $attributeInfo->save();
        }
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
