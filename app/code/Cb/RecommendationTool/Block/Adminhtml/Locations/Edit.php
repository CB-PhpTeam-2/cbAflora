<?php

namespace Cb\RecommendationTool\Block\Adminhtml\Locations;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	protected $_coreRegistry = null;

	public function __construct(
		Context $context,
		Registry $registry,
		array $data = []
	)
	{
		$this->_coreRegistry = $registry;
		parent::__construct($context, $data);
	}

	protected function _construct(){
		$this->_objectId = 'id';
		$this->_blockGroup = 'Cb_RecommendationTool';
		$this->_controller = 'adminhtml_locations';

		parent::_construct();
		if ($this->_isAllowedAction('Cb_RecommendationTool::save')) {
			$this->buttonList->update('save', 'label', __('Save Recommendation Tool'));
			$this->buttonList->add(
				'saveandcontinue',
				[
					'label' => __('Save and Continue Edit'),
					'class' => 'save save-form',
					'data_attribute' => [
						'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
					]
				],
				-100
			);
		} else {
			$this->buttonList->remove('save');
		}

		if ($this->_isAllowedAction('Cb_RecommendationTool::locations_delete')) {
			$this->buttonList->update('delete', 'label', __('Delete Recommendation Tool'));
		} else {
			$this->buttonList->remove('delete');
		}

		$this->buttonList->remove('back');
		$this->addButton(
			'back',
			[
				'label' => __('Back'),
				'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
				'class' => 'back back-form'
			],
			-1
		);

		$this->buttonList->remove('reset');
		$this->addButton(
			'reset',
			[
				'label' => __('Reset'),
				'onclick' => 'setLocation(window.location.href)',
				'class' => 'reset reset-form'
			],
			-1
		);
	}

	public function getHeaderText()
	{
		if ($this->_coreRegistry->registry('postcode_locations')->getId()) {
			return __("Edit Locations '%1'", $this->escapeHtml($this->_coreRegistry->registry('postcode_locations')->getTitle()));
		} else {
			return __('Add New Locations');
		}
	}

	protected function _getSaveAndContinueUrl()
	{
		return $this->getUrl('postcode/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
	}

	protected function _isAllowedAction($resourceId)
	{
		return $this->_authorization->isAllowed($resourceId);
	}

	protected function _prepareLayout()
	{
		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('locations_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'locations_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'locations_content');
                }
            }
        ";
		return parent::_prepareLayout();
	}
}