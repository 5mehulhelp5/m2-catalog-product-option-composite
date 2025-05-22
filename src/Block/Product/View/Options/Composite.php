<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Product\View\Options;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Composite extends Template
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Json */
    protected $json;

    /** @var Variables */
    protected $variables;

    /** @var Product */
    private $product;

    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        Json $json,
        Variables $variables,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->registryHelper = $registryHelper;
        $this->json = $json;
        $this->variables = $variables;
    }

    public function getProduct(): Product
    {
        if (! $this->product) {
            if ($this->registryHelper->registry('current_product')) {
                $this->product = $this->registryHelper->registry('current_product');
            } else {
                throw new \LogicException('Product is not defined');
            }
        }

        return $this->product;
    }

    public function getConfig(): string
    {
        $config = [];

        /** @var Option $option */
        foreach ($this->getProduct()->getOptions() as $option) {
            $optionValues = $option->getValues();

            if ($optionValues) {
                /** @var Option\Value $optionValue */
                foreach ($optionValues as $optionValue) {
                    $allowHideProductIds = $optionValue->getData('allow_hide_product_ids');

                    if (! $this->variables->isEmpty($allowHideProductIds)) {
                        $optionId = $option->getId();
                        $optionValueId = $optionValue->getId();

                        if ($option->getProduct()->getTypeId() === Type::TYPE_CODE) {
                            foreach ($allowHideProductIds as $allowHideProductId) {
                                if (preg_match(
                                    '/[0-9]+_[0-9]+/',
                                    $allowHideProductId
                                )) {
                                    [$bundleOptionId, $bundleProductId] = explode(
                                        '_',
                                        $allowHideProductId
                                    );

                                    $config[ 'allowHideProductIds' ][ $optionId ][ $optionValueId ][ $bundleOptionId ][] =
                                        $bundleProductId;
                                }
                            }
                        } else {
                            $config[ 'allowHideProductIds' ][ $optionId ][ $optionValueId ] = $allowHideProductIds;
                        }
                    }
                }
            }
        }

        return $this->json->encode($config);
    }
}
