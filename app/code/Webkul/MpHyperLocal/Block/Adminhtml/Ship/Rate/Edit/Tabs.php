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
namespace Webkul\MpHyperLocal\Block\Adminhtml\Ship\Rate\Edit;

/**
 * Ship rate page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Ship Rate Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $amzAccountId = (int)$this->getRequest()->getParam('id');
        
        $this->addTab(
            'edit_form',
            [
                'label' => __('Ship Rate Info'),
                'title' => __('Ship Rate Info'),
                'content' => $this->getLayout()
                                    ->createBlock(
                                        \Webkul\MpHyperLocal\Block\Adminhtml\Ship\Rate\Edit\Tab\Main::class
                                    )
                                    ->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
