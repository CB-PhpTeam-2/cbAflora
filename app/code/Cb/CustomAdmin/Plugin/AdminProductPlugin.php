<?php
namespace Cb\CustomAdmin\Plugin;

use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;

/**
 * Data provider for "Customizable Options" panel
 */
class AdminProductPlugin
{
    protected $_moduleHelper;

    public function __construct(
        \Cb\GreenlineApi\Helper\Data $moduleHelper
    ){
        $this->_moduleHelper = $moduleHelper;
    }

    public function afterGetData(ProductDataProvider $subject, $meta) {
        $result = $meta;
        $defaultSellerId = $this->_moduleHelper->getDefaultSellerId();
        $result['']['product']['assign_seller']['seller_id'] = $defaultSellerId;

        return $result;
    }
}

?>