<?php
namespace Trootech\General\Plugin\Swatches\Block\Product\Renderer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use  Magento\Catalog\Helper\Image; 
class Configurable
{
    /**
     * @var \Magento\Catalog\Helper\Output
    */
    protected $catalogHelper;

    /**
     * @var ProductRepositoryInterface
    */
    protected $productRepository; 

    protected $image;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Helper\Output $catalogHelper,
        Image  $image,
        ProductRepositoryInterface $productRepository
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->productRepository = $productRepository;
        $this->image = $image;
    }

       /* public function getProductById($id)
        {
        return $this->productRepository->getById($id);
        }*/

    public function afterGetJsonConfig(\Magento\Swatches\Block\Product\Renderer\Configurable $subject, $result) {
 
        $jsonResult = json_decode($result, true);
        $jsonResult['img'] = [];
        foreach ($subject->getAllowProducts() as $simpleProduct) {
            $product = $this->productRepository->getById($simpleProduct->getId());
            $imageUrl =$this->image->init($product, 'product_base_image')->constrainOnly(FALSE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->getUrl();
            $jsonResult['img'][$product->getId()] = $imageUrl; 
        }
        $result = json_encode($jsonResult);
        return $result;
    }
}