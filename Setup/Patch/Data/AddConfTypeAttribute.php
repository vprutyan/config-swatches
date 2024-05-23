<?php
/**
 * Copyright © Brander, Inc. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Brander\ConfSwatches\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Psr\Log\LoggerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config as EavConfig;
use Brander\ConfSwatches\Model\Attribute\Converter\Swatches as SwatchesConverter;

/**
 * Class AddConfTypeAttribute for Create New Product Attribute
 */
class AddConfTypeAttribute implements DataPatchInterface {

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var SwatchesConverter
     */
    private $swatchesConverter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param EavConfig $eavConfig
     * @param SwatchesConverter $swatchesConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        EavConfig $eavConfig,
        SwatchesConverter $swatchesConverter,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->swatchesConverter = $swatchesConverter;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        try {
            $eavSetup->addAttribute(Product::ENTITY, 'conf_type', [
                'type' => 'int',
                'label' => 'Conf Type',
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'apply_to' => implode(',', [ProductType::TYPE_SIMPLE, ProductType::TYPE_VIRTUAL, Configurable::TYPE_CODE]),
                'option' => [
                    'values' => [
                        'Small',
                        'Middle',
                        'Big'
                    ]
                ]
            ]);

            $this->eavConfig->clear();
            $this->swatchesConverter->convertAttributeToSwatches('conf_type');

            $this->addAttributeToSets($eavSetup, 'conf_type');
            $this->addAttributeToSets($eavSetup, 'color');
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Add product attribute to all attribute sets
     *
     * @param EavSetup $eavSetup
     * @param string $attributeName
     */
    private function addAttributeToSets($eavSetup, $attributeName)
    {
        $attributeGroup = 'General';
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $allAttributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($allAttributeSetIds as $attributeSetId) {
            $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $attributeGroup);
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupId,
                $attributeName,
                null
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
