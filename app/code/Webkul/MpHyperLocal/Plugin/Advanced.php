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
namespace Webkul\MpHyperLocal\Plugin;

use \Webkul\MpHyperLocal\Helper\Data;

class Advanced
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;
    
    /**
     * @param \Webkul\MpHyperLocal\Helper\Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function afterGetProductCollection(\Magento\CatalogSearch\Model\Advanced $subject, $result)
    {
        $savedAddress = $this->helper->getSavedAddress();
        if ($savedAddress) {
            $sellerIds = $this->helper->getNearestSellers();
            $allowedProList = $this->helper->getNearestProducts($sellerIds);
            $result->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
        }
        return $result;
    }
}
