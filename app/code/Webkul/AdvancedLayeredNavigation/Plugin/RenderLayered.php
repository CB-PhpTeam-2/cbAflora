<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Plugin;

use Magento\Eav\Model\Entity\Attribute;

class RenderLayered
{
    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_request = $request;
    }

    /**
     * @param \Webkul\AdvancedLayeredNavigation\Helper\Data $subject
     * @param $result
     * @return string
     */
    public function afterGetSwatchData(\Magento\Swatches\Block\LayeredNavigation\RenderLayered $subject, $result)
    {
        $result['selected_value'] =  $this->_request->getParam($result['attribute_code']);
        return $result;
    }
}
