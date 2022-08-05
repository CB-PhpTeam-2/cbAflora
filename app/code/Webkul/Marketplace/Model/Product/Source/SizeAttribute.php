<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model\Product\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\Config;

/**
 * Class Producttype is used tp get the product type options
 */
class SizeAttribute implements OptionSourceInterface
{

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attribute = $this->_eavConfig->getAttribute('catalog_product', 'size');
        $options = $attribute->getSource()->getAllOptions();
        $optionsExists = array();
        /*foreach ($options as $option) {
            if ($option['value'] > 0) {
                $optionsExists[] = $option['label'];
            }
        }*/
        
        return $options;
    }
}
