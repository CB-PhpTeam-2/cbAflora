<?php
namespace Cb\RecommendationTool\Block\Adminhtml\Locations\Grid\Renderer;

class Country extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_countryFactory;

    public function __construct(
            \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->_countryFactory = $countryFactory;
    }

    /**
    * Renders grid column
    * @param \Magento\Framework\DataObject $row
    * @return string
    */
    public function render(\Magento\Framework\DataObject $row)
    {
        $countryCode = $row->getData('country');
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        
        return $country->getName();
   
    }
}