<?php
declare(strict_types=1);

namespace Foundation\Cms\Block;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Product\ImageFactory as ImageFactory;
use Magento\Catalog\Helper\Output as CatalogHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class FeaturedProduct
 *
 * @package  Foundation\Cms\Block
 */
class FeaturedProduct extends Template
{
    /**
    * @var string
    */
    const CONFIG_ACTIVE = 'foundation_cms/featured_products/active';

    /**
    * @var string
    */
    const CONFIG_SKU = 'foundation_cms/featured_products/sku';

    /**
     * @var string
     */
    protected $_template = "Foundation_Cms::featured-products.phtml";

    /**
     * @var array|Collection
     */
    protected array|Collection $products = [];

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $productCollectionFactory;

    /**
     * @var ImageFactory
     */
    protected ImageFactory $imageFactory;

    /**
     * @var CatalogHelper
     */
    protected CatalogHelper $catalogHelper;

    /**
     * FeaturedProduct constructor.
     *
     * @param Template\Context  $context
     * @param CollectionFactory $productCollectionFactory
     * @param ImageFactory      $imageFactory
     * @param CatalogHelper     $catalogHelper
     * @param array             $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        ImageFactory $imageFactory,
        CatalogHelper $catalogHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageFactory = $imageFactory;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @return $this|FeaturedProduct
     */
    protected function _beforeToHtml()
    {
        if (!$this->getProducts() || !$this->isActive()) {
            $this->setTemplate(null);
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getSku(): array
    {
        $sku = $this->_scopeConfig->getValue(self::CONFIG_SKU, ScopeInterface::SCOPE_STORE);
        if(!$sku) { return []; }

        return explode(',', str_replace(' ', '', $sku));
    }

    /**
     * @return bool
     */
    protected function isActive(): bool
    {
        return (bool) $this->_scopeConfig->getValue(self::CONFIG_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return Collection|array
     */
    public function getProducts(): Collection|array
    {

        if (!$this->products) {

            $sku = $this->getSku();

            if(empty($sku)) { return []; }

            /**
             * @var Collection $products
             */
            $collection = $this->productCollectionFactory->create()
                ->addStoreFilter($this->getStoreId())
                ->addAttributeToSelect(['sku','name','status', 'description', 'image'])
                ->addPriceData()
                ->addAttributeToFilter('sku', ['in' => $sku]);

            $this->products = $collection->getItems();

        }

        return $this->products;

    }


    /**
     * @param  $product
     * @param  array $attributes
     * @return string
     */
    public function getImageHtml($product, array $attributes = []): string
    {
        return $this->imageFactory->create($product, 'product_base_image', $attributes)->toHtml();
    }


    /**
     * @param  \Magento\Catalog\Model\Product $product
     * @param  array                          $options
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPriceHtml(\Magento\Catalog\Model\Product $product, array $options = []): string
    {
        if (!isset($options['zone'])) {
            $options['zone'] = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST;
        }

        /**
         * @var \Magento\Framework\Pricing\Render $priceRender
        */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $options
            );
        }

        return $price;
    }

    /**
     * @param  $product
     * @return string
     */
    public function getProductDesc($product): string
    {
        /* @noEscape */
        try {
            return $this->catalogHelper->productAttribute(
                $product,
                $product->getDescription(),
                'description'
            );
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * @return int
     */
    protected function getStoreId(): int
    {
        if ($this->hasData('store_id')) {
            return (int)$this->_getData('store_id');
        }
        return (int)$this->_storeManager->getStore()->getId();
    }


}
