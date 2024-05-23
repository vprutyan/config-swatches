<?php
/**
 * Copyright © Brander, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brander\ConfSwatches\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Provider
{
    const XML_CUSTOM_SWATCH_PRESELECT = 'brander_swatches/general/is_enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if custom swatch preselect is enabled
     *
     * @return bool
     */
    public function isSwatchPreselectEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_CUSTOM_SWATCH_PRESELECT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
    }
}
