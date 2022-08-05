<?php

namespace Cb\RecommendationTool\Block\Adminhtml\Locations;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Cb\RecommendationTool\Model\Locations;
use Cb\RecommendationTool\Model\ResourceModel\Locations\CollectionFactory;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	protected $_collectionFactory;

	protected $_locations;

	protected $_pageLayoutBuilder;

	public function __construct(
		Context $context,
		Data $backendHelper,
		Locations $locations,
		CollectionFactory $collectionFactory,
		BuilderInterface $pageLayoutBuilder,
		array $data = []
	) {
		$this->_collectionFactory = $collectionFactory;
		$this->_locations = $locations;
		$this->_pageLayoutBuilder = $pageLayoutBuilder;
		parent::__construct($context, $backendHelper, $data);
	}

	public function _construct()
	{	
		parent::_construct();
		$this->setId('locationsGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
	}

	protected function _prepareCollection()
	{
		$collection = $this->_collectionFactory->create();
		/* @var $collection \Magento\Cms\Model\ResourceModel\Page\Collection */
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn(
			'id',
			[
				'header'    => __('ID'),
				'index'     => 'id',
			]
		);

		$this->addColumn(
			'frontend_label',
			[
				'header'    => __('Frontend Label'),
				'index'     => 'frontend_label',
			]
		);

		$this->addColumn(
			'backend_label',
			[
				'header'    => __('Backend Label'),
				'index'     => 'backend_label'
			]
		);

		$this->addColumn(
			'attribute_code',
			[
				'header'    => __('Attribute Code'),
				'index'     => 'attribute_code'
			]
		);

		$this->addColumn(
			'locations_actions',
			[
				'header' => __('Action'),
				'type' => 'action',
				'getter' => 'getId',
				'actions' => [
					[
						'caption' => __('Edit'),
						'url' => [
							'base' => '*/*/edit',
							'params' => ['store' => $this->getRequest()->getParam('store')]
						],
						'field' => 'id'
					]
				],
				'sortable' => false,
				'filter' => false,
				'index' => 'stores',
				'header_css_class' => 'col-action',
				'column_css_class' => 'col-action'
			]
		);

		parent::_prepareColumns();
	}

	/**
	 * @return $this
	 */
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('locations_param');

		$this->getMassactionBlock()->addItem(
			'delete',
			[
				'label' => __('Delete'),
				'url' => $this->getUrl('postcode/*/massDelete'),
				'confirm' => __('Are you sure?')
			]
		);

		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
	}
}