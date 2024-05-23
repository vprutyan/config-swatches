<?php
/**
 * Copyright © Brander, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brander\ConfSwatches\Plugin\Product\View;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Brander\ConfSwatches\Model\Config\Provider as ConfigProvider;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;

class ConfigPlugin
{
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param JsonSerializer $jsonSerializer
     * @param ConfigProvider $configProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonSerializer $jsonSerializer,
        ConfigProvider $configProvider,
        LoggerInterface $logger
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->configProvider = $configProvider;
        $this->logger = $logger;
    }

    /**
     * Add custom swatch setting to js config
     *
     * @param Configurable $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig($subject, $result)
    {
        try {
            $config = $this->jsonSerializer->unserialize($result);
            if ($this->configProvider->isSwatchPreselectEnabled()) {
                $currentProduct = $subject->getProduct();
                $config = $this->getPreselectedSwatchOptions($currentProduct, $config);
            } else {
                $config['isSwatchPreselectEnabled'] = 0;
            }
            return $this->jsonSerializer->serialize($config);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $result;
    }

    /**
     * Retrieve preselected custom swatches
     *
     * @param Product $currentProduct
     * @param array $config
     * @return array
     */
    private function getPreselectedSwatchOptions($currentProduct, $config)
    {
        $attributes = isset($config['attributes']) && is_array($config['attributes']) && count($config['attributes']) > 0 ? $config['attributes'] : false;
        $swatches = [];
        $config['preselected_swatches'] = '';
        $config['isSwatchPreselectEnabled'] = 0;
        if ($attributes) {
            $children = $currentProduct->getTypeInstance()->getUsedProducts($currentProduct);
            foreach ($children as $child) {
                if ($child->isSalable()) {
                    foreach ($attributes as $attribute) {
                        $swatches[$attribute['code']] = $child->getData($attribute['code']);
                    }
                    $config['preselected_swatches'] = $swatches;
                    $config['isSwatchPreselectEnabled'] = 1;
                    break;
                }
            }
        }
        return $config;
    }
}
