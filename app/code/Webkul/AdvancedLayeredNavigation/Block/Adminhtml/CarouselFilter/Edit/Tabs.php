<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Block\Adminhtml\CarouselFilter\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('carouselFilter_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Add Carousel Filter'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'carouselFilter_info',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\AdvancedLayeredNavigation\Block\Adminhtml\CarouselFilter\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );
        if ($this->getRequest()->getParam('id')) {
            $this->addTab(
                'options',
                [
                    'label' => __('Options'),
                    'title' => __('Options'),
                    'content' => $this->getLayout()->createBlock(
                        \Webkul\AdvancedLayeredNavigation\Block\Adminhtml\CarouselFilter\Edit\Tab\Options::class
                    )->toHtml(),
                    'active' => false
                ]
            );
        }
        return parent::_beforeToHtml();
    }
}
