<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton block back button
 */
class BackButton extends Generic implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $button = [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrlBack()),
            'class' => 'back',
            'sort_order' => 10
        ];
        return $button;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getUrlBack()
    {
        return $this->getUrl('*/*/');
    }
}
