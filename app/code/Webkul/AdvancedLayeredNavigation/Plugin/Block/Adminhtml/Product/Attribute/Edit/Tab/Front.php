<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Plugin\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class Front
{

    /**
     * @param \Webkul\AdvancedLayeredNavigation\Model\NavigationAttributes\Source\ViewType $navAttr
     * @param Attribute $attributeFactory
     */
    public function __construct(
        \Webkul\AdvancedLayeredNavigation\Model\NavigationAttributes\Source\ViewType $navAttr,
        Attribute $attributeFactory
    ) {
        $this->navAttr = $navAttr;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject,
        \Closure $proceed
    ) {
        $priceType = false;
        $valueToShow = 0;
        $postData = $subject->getRequest()->getParams();
        if (!empty($postData['attribute_id'])) {
            $attributeId = $postData['attribute_id'];
            $attributeInfo = $this->attributeFactory->load($attributeId);
            if ($attributeInfo->getFrontendInput() == 'price') {
                $priceType = true;
                $attributeInfo->setIsDisplay(0)->save();
            }
            if ($attributeInfo->getId()) {
                $valueToShow = $attributeInfo->getIsDisplay();
            }
        }

        $isDisplaySource = $this->navAttr->toOptionArray();
        $form = $subject->getForm();
        if (is_object($form) && empty($priceType)) {
            $fieldset = $form->getElement('front_fieldset');
            $fieldset->addField(
                'is_display',
                'select',
                [
                    'name' => 'is_display',
                    'label' => __('Display Type'),
                    'title' => __('Display Type'),
                    'note' => __('To change the filter view to horizontal or vertical'),
                    'values' => $isDisplaySource,
                    'value' => $valueToShow
                ]
            );
        }

        return $proceed();
    }
}
