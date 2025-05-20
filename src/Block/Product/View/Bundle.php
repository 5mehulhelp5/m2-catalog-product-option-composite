<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Product\View;

use FeWeDev\Base\Json;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Bundle extends Template
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Json */
    protected $json;

    /** @var Product|null */
    private $product = null;

    public function __construct(Template\Context $context, Registry $registryHelper, Json $json, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->registryHelper = $registryHelper;
        $this->json = $json;
    }

    public function getProduct(): ?Product
    {
        if ($this->product === null) {
            $this->product = $this->registryHelper->registry('product');
        }

        return $this->product;
    }

    public function getIndex(): string
    {
        $index = [];

        $product = $this->getProduct();

        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        $typeInstance->setStoreFilter(
            $product->getStoreId(),
            $product
        );

        $options = $typeInstance->getOptionsCollection($product);

        /** @var Option $option */
        foreach ($options as $option) {
            $optionSelections = $typeInstance->getSelectionsCollection(
                [$option->getId()],
                $product
            );

            /** @var Product $optionSelection */
            foreach ($optionSelections as $optionSelection) {
                $index[ $option->getId() ][ $optionSelection->getData('selection_id') ] = $optionSelection->getId();
            }
        }

        return $this->json->encode($index);
    }
}
