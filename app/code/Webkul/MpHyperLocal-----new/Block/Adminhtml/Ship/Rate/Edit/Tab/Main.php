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
namespace Webkul\MpHyperLocal\Block\Adminhtml\Ship\Rate\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('ship_');
        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Ship Rate Information')]);
        
        $baseFieldset->addField(
            'address',
            'file',
            [
                'name' => 'rate-csv-file',
                'label' => __('Ship Rate'),
                'id' => 'rate-csv-file',
                'title' => __('Ship Rate'),
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'sample_file',
            'link',
            [
                'href' => $this->getViewFileUrl('Webkul_MpHyperLocal::hyper-local-shiprate.csv'),
                'value'  => 'Download Sample File'
            ]
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
