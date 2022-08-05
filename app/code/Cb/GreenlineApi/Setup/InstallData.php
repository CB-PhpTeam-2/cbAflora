<?php
namespace Cb\GreenlineApi\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Api\AttributeManagementInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;
	private $_eavConfig;
	private $attributeManagement;

	public function __construct(
		EavSetupFactory $eavSetupFactory,
		\Magento\Eav\Model\Config $eavConfig,
		AttributeManagementInterface $attributeManagement
	)
	{
		$this->eavSetupFactory = $eavSetupFactory;
		$this->_eavConfig = $eavConfig;
		$this->attributeManagement = $attributeManagement;
	}
	
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		
		$exist = '';
		$exist = $this->isProductAttributeExists('greenlineapi_product_id');

		if($exist == ''){
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Product::ENTITY,
				'greenlineapi_product_id',
				[
					'type' => 'varchar',
					'backend' => '',
					'frontend' => '',
					'label' => 'Greenline Product Id',
					'input' => 'text',
					'class' => '',
					'source' => '',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
					'visible' => true,
					'required' => false,
					'user_defined' => false,
					'default' => '',
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'unique' => false,
					'apply_to' => ''
				]
			);

			$ATTRIBUTE_CODE = 'greenlineapi_product_id';
			$ATTRIBUTE_GROUP = 'General';
			$obj = \Magento\Framework\App\ObjectManager::getInstance();
			$config = $obj->get('Magento\Catalog\Model\Config');

	        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
			$attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
			foreach ($attributeSetIds as $attributeSetId) {
			    if ($attributeSetId) {
			        $group_id = $config->getAttributeGroupId($attributeSetId, $ATTRIBUTE_GROUP);
			        $this->attributeManagement->assign(
			            'catalog_product',
			            $attributeSetId,
			            $group_id,
			            $ATTRIBUTE_CODE,
			            999
			       );
			    }
			}
		}
        
        $setup->endSetup();

	}

	public function isProductAttributeExists($field)
    {
        $attr = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
 
        return ($attr && $attr->getId());
    }
}