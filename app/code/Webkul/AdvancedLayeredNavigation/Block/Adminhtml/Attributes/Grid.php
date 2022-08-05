<?php
/**
 * @category   Webkul
 * @package    Webkul_AdvancedLayeredNavigation
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Block\Adminhtml\Attributes;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid as AttributesGrid;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends AttributesGrid
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $collectionFactory, $data);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('attribute_id');
        $this->getMassactionBlock()->setFormFieldName('advancedlayerednavigation');
    
        $groups = [
            '0' => __('No'),
            '1' => __('Filterable (With results)'),
            '2' => __('Filterable (No results)')
        ];
        
        array_unshift($groups, ['label'=> '', 'value'=> '']);
        $this->getMassactionBlock()->addItem('assign_group', [
                'label'        => __('Use in layered navigation'),
                'url'          => $this->getUrl('advancedlayerednavigation/attributes/assign'),
                'additional'   => [
                    'visibility'    => [
                        'name'     => 'group',
                        'type'     => 'select',
                        'class'    => 'required-entry',
                        'confirm'  => __('Are you sure?'),
                        'label'    => __(''),
                        'values'   => $groups
                    ]
                ]
        ]);
        $displayGroups = [
            '0' => __('View Vertical'),
            '1' => __('View Horizontal')
        ];
        
        array_unshift($groups, ['label'=> '', 'value'=> '']);
        $this->getMassactionBlock()->addItem('assign_display_type', [
                'label'        => __('Display Type'),
                'url'          => $this->getUrl('advancedlayerednavigation/attributes/displaytype'),
                'additional'   => [
                    'visibility'    => [
                        'name'     => 'group',
                        'type'     => 'select',
                        'class'    => 'required-entry',
                        'confirm'  => __('Are you sure?'),
                        'label'    => __(''),
                        'values'   => $displayGroups
                    ]
                ]
        ]);
    
        return $this;
    }
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'is_display',
            [
                'header' => __('Display'),
                'sortable' => true,
                'index' => 'is_display',
                'type' => 'options',
                'options' => ['1' => __('Horizontal'), '0' => __('Vertical')],
                'align' => 'center'
            ],
            'is_display'
        );
    }
}
