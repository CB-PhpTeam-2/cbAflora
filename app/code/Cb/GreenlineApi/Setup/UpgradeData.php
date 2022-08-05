<?php
namespace Cb\GreenlineApi\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Api\AttributeManagementInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
    private $_eavConfig;
    private $attributeManagement;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        AttributeManagementInterface $attributeManagement
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_eavConfig = $eavConfig;
        $this->attributeManagement = $attributeManagement;
    }

    public function upgrade(ModuleDataSetupInterface $setup,ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $exist = '';
            $exist = $this->isProductAttributeExists('barcode');

            if($exist == ''){
                $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'barcode',
                    [
                        'type'     => 'varchar',
                        'label'    => 'Barcode',
                        'input'    => 'text',
                        'source'   => '',
                        'visible'  => true,
                        'default'  => '',
                        'required' => false,
                        'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group'    => 'General',
                    ]
                );

                $ATTRIBUTE_CODE = 'barcode';
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
        }

        $setup->endSetup();
    }

    public function isProductAttributeExists($field)
    {
        $attr = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
 
        return ($attr && $attr->getId());
    }

}
?>