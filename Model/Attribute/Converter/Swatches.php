<?php
/**
 * Copyright © Brander, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brander\ConfSwatches\Model\Attribute\Converter;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Class Swatches
 */
class Swatches
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var array
     */
    protected $optionCollection = [];

    /**
     * @var array
     */
    protected $colorMap = [
        'Black'     => '#000000',
        'Blue'      => '#1857f7',
        'Brown'     => '#945454',
        'Gray'      => '#8f8f8f',
        'Green'     => '#53a828',
        'Lavender'  => '#ce64d4',
        'Multi'     => '#ffffff',
        'Orange'    => '#eb6703',
        'Purple'    => '#ef3dff',
        'Red'       => '#ff0000',
        'White'     => '#ffffff',
        'Yellow'    => '#ffd500',
    ];

    /**
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param Config $eavConfig
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        CollectionFactory $attrOptionCollectionFactory,
        Config $eavConfig,
        ProductAttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * @param string $attributeCode
     */
    public function convertAttributeToSwatches($attributeCode) {
        try {
            /** @var EavAttribute $attribute */
            $attribute = $this->attributeRepository->get($attributeCode);
            if (!$attribute) {
                return;
            }

            $attributeData['option'] = $this->addExistingOptions($attribute);
            $attributeData['frontend_input'] = 'select';
            $attributeData['swatch_input_type'] = 'text';
            $attributeData['update_product_preview_image'] = 1;
            $attributeData['use_product_image_for_swatch'] = 0;
            $attributeData['optiontext'] = $this->getOptionSwatch($attributeData);
            $attributeData['defaulttext'] = $this->getOptionDefaultText($attributeData);
            $attributeData['swatchtext'] = $this->getOptionSwatchText($attributeData);
            $attribute->addData($attributeData);
            $this->attributeRepository->save($attribute);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param array $attributeData
     * @return array
     */
    protected function getOptionSwatch($attributeData)
    {
        $optionSwatch = ['order' => [], 'value' => [], 'delete' => []];
        $i = 0;
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            $optionSwatch['delete'][$optionKey] = '';
            $optionSwatch['order'][$optionKey] = (string)$i++;
            $optionSwatch['value'][$optionKey] = [$optionValue, ''];
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    protected function getOptionSwatchVisual($attributeData)
    {
        $optionSwatch = ['value' => []];
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            if (substr($optionValue, 0, 1) == '#' && strlen($optionValue) == 7) {
                $optionSwatch['value'][$optionKey] = $optionValue;
            } else if (!empty($this->colorMap[$optionValue])) {
                $optionSwatch['value'][$optionKey] = $this->colorMap[$optionValue];
            } else {
                $optionSwatch['value'][$optionKey] = $this->colorMap['White'];
            }
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    protected function getOptionDefaultVisual($attributeData)
    {
        $optionSwatch = $this->getOptionSwatchVisual($attributeData);
        return [array_keys($optionSwatch['value'])[0]];
    }

    /**
     * @param array $attributeData
     * @return array
     */
    protected function getOptionSwatchText($attributeData)
    {
        $optionSwatch = ['value' => []];
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            $optionSwatch['value'][$optionKey] = [$optionValue, ''];
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    protected function getOptionDefaultText($attributeData)
    {
        $optionSwatch = $this->getOptionSwatchText($attributeData);
        return [array_keys($optionSwatch['value'])[0]];
    }

    /**
     * @param $attributeId
     * @return void
     */
    protected function loadOptionCollection($attributeId)
    {
        if (empty($this->optionCollection[$attributeId])) {
            $this->optionCollection[$attributeId] = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($attributeId)
                ->setPositionOrder('asc', true)
                ->load();
        }
    }

    /**
     * @param EavAttribute $attribute
     * @return array
     */
    protected function addExistingOptions($attribute)
    {
        $options = [];
        $attributeId = $attribute->getId();
        if ($attributeId) {
            $this->loadOptionCollection($attributeId);
            /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
            foreach ($this->optionCollection[$attributeId] as $option) {
                $options[$option->getId()] = $option->getValue();
            }
        }
        return $options;
    }
}
