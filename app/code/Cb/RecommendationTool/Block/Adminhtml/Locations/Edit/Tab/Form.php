<?php

namespace Cb\RecommendationTool\Block\Adminhtml\Locations\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
	protected $_systemStore;
	protected $_countryFactory;
	protected $_regionFactory;

	public function __construct(
		Context $context,
		Registry $registry,
		FormFactory $formFactory,
		Store $systemStore,
		\Magento\Directory\Model\Config\Source\Country $countryFactory,
		\Magento\Directory\Model\RegionFactory $regionFactory,
		array $data = []
	) {
		$this->_systemStore = $systemStore;
		$this->_countryFactory = $countryFactory;
		$this->_regionFactory = $regionFactory;
		parent::__construct($context, $registry, $formFactory, $data);
	}

	protected function _prepareForm()
	{	
		$model = $this->_coreRegistry->registry('postcode_locations');
		/*
         * Checking if user have permissions to save information
         */
		if ($this->_isAllowedAction('Cb_RecommendationTool::save')) {
			$isElementDisabled = false;
		} else {
			$isElementDisabled = true;
		}

		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create();

		$fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

		if ($model->getId()) {
			$fieldset->addField('id', 'hidden', ['name' => 'id']);
		}
		
		$fieldset->addField(
			'frontend_label',
			'text',
			[
				'name' => 'frontend_label',
				'label' => __('Frontend Label'),
				'title' => __('Frontend Label'),
				'id' => 'frontend_label',
				'class' => 'frontend_label',
				'required' => true,
				'disabled' => $isElementDisabled
			]
		);

		$fieldset->addField(
			'backend_label',
			'text',
			[
				'name' => 'backend_label',
				'label' => __('Backend Label'),
				'title' => __('Backend Label'),
				'id' => 'backend_label',
				'class' => 'backend_label',
				'required' => true,
				'disabled' => $isElementDisabled
			]
		);

		$fieldset->addField(
			'attribute_code',
			'text',
			[
				'name' => 'attribute_code',
				'label' => __('Attribute Code'),
				'title' => __('Attribute Code'),
				'id' => 'attribute_code',
				'class' => 'attribute_code',
				'required' => true,
				'disabled' => $isElementDisabled
			]
		);

		$this->_eventManager->dispatch('adminhtml_locations_edit_tab_form_prepare_form', ['form' => $form]);

		$form->setValues($model->getData());
		$this->setForm($form);

		parent::_prepareForm();
	}

	/**
	 * Prepare label for tab
	 *
	 * @return \Magento\Framework\Phrase
	 */
	public function getTabLabel()
	{
		return __('LegendTool');
	}

	/**
	 * Prepare title for tab
	 *
	 * @return \Magento\Framework\Phrase
	 */
	public function getTabTitle()
	{
		return __('LegendTool');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canShowTab()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHidden()
	{
		return false;
	}

	/**
	 * Check permission for passed action
	 *
	 * @param string $resourceId
	 * @return bool
	 */
	protected function _isAllowedAction($resourceId)
	{
		return $this->_authorization->isAllowed($resourceId);
	}
}