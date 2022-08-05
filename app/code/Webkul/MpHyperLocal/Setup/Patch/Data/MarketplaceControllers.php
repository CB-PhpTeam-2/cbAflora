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
namespace Webkul\MpHyperLocal\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class MarketplaceControllers implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Webkul\Marketplace\Model\ControllersRepository
     */
    protected $controllersRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $data = [];
        if (!($this->controllersRepository->getByPath('mphyperlocal/account/addrate')->getSize())) {
            $data[] = [
                'module_name' => 'Webkul_MpHyperLocal',
                'controller_path' => 'mphyperlocal/account/addrate',
                'label' => 'Shipping Rates',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!($this->controllersRepository->getByPath('mphyperlocal/account/origin')->getSize())) {
            $data[] = [
                'module_name' => 'Webkul_MpHyperLocal',
                'controller_path' => 'mphyperlocal/account/origin',
                'label' => 'Seller Origin',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!empty($data)) {
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable('marketplace_controller_list'),
                $data
            );
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
