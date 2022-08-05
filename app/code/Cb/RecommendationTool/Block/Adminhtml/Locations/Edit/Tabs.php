<?php

namespace Cb\RecommendationTool\Block\Adminhtml\Locations\Edit;

use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Registry;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
	const BASIC_TAB_GROUP_CODE = 'basic';
	/**
	 * @var InlineInterface
	 */
	protected $_translateInline;

	public function __construct(
		Context $context,
		Session $authSession,
		Registry $registry,
		EncoderInterface $jsonEncoder,
		InlineInterface $translateInline,
		array $data = []
	){
		$this->_coreRegistry = $registry;
		$this->_translateInline = $translateInline;
		parent::__construct($context, $jsonEncoder, $authSession, $data);
	}
	
	protected function _construct()
	{
		parent::_construct();
		$this->setId('locations_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle("".__('Tool Information'));
		if ($tab = $this->getRequest()->getParam('activeTab' ))
			$this->_activeTab = $tab;
		else
			$this->_activeTab = 'locations';
	}

	protected function _translateHtml($html)
	{
		$this->_translateInline->processResponseBody($html);
		return $html;
	}

	/**
	 * @return $this
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	protected function _prepareLayout()
	{
		$modelGroup = $this->_coreRegistry->registry('postcode_locations');
		$this->addTab(
			'locations',
			[
				'label' => __('Generral Information'),
				'title' => __('Generral Information'),
				'content' => $this->_getTabHtml('\Form'),
				'group_code' => self::BASIC_TAB_GROUP_CODE
			]
		);

		return parent::_prepareLayout();
	}

	private function _getTabHtml($tab)
	{
		return $this->getLayout()->createBlock('\Cb\RecommendationTool\Block\Adminhtml\Locations\Edit\Tab' . $tab )->toHtml();
	}
}