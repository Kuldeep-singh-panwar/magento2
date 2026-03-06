<?php
namespace Testing\CustomDashboard\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Image;

class Dashboard extends Template
{
    protected $productCollectionFactory;
    protected $imageHelper;

    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Image $imageHelper,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    public function getActiveMenu()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * Get all products
     */
    public function getProducts()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        // Remove filters for testing
        // $collection->addAttributeToFilter('status', 1)
        //     ->addAttributeToFilter('visibility', ['in' => [...]]);
        return $collection;
    }

    /**
     * Get product image URL
     */
    public function getProductImageUrl($product, $width = 200, $height = 200)
    {
        return $this->imageHelper->init($product, 'product_page_image_large')
            ->resize($width, $height)
            ->getUrl();
    }

    /**
     * Get product details for popup
     */
    public function getProductDetails($product)
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $this->getFormattedPrice($product->getFinalPrice()),
            'description' => $product->getShortDescription(),
            'image' => $this->getProductImageUrl($product, 300, 300),
            'url' => $product->getProductUrl()
        ];
    }

    /**
     * Format price
     */
    public function getFormattedPrice($price)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->format($price, [], false);
    }

    /**
     * Get add to cart URL
     */
    public function getAddToCartUrl($product)
    {
        return $this->getUrl('checkout/cart/add', ['product' => $product->getId()]);
    }
}