<?php
namespace Webkul\AdvancedLayeredNavigation\ViewModel;

class AdvancedNavViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected $advanceNavHelper;
    protected $jsonHelper;
    protected $catalogHelper;
    public function __construct(
        \Webkul\AdvancedLayeredNavigation\Helper\Data $advanceNavHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute
    ) {
        $this->advanceNavHelper= $advanceNavHelper;
        $this->jsonHelper= $jsonHelper;
        $this->catalogHelper = $catalogHelper;
        $this->_entityAttribute = $entityAttribute;
    }

    public function getAdvanceNavHelper()
    {
        return $this->advanceNavHelper;
    }

    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }

    public function checkAttributeDisplayType($attributeCode)
    {
        /*Get attribute details*/
        return  $attributeDetails = $this->_entityAttribute->loadByCode("catalog_product", $attributeCode);
    }
}
