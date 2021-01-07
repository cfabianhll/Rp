<?php

namespace Trootech\General\Block;


class Slider extends \Magento\Framework\View\Element\Template
{
	protected $categories;
	
	protected $_productColl;
	
	protected $_productCol;
	
	protected $_productCollec;
	
	protected $_backproductCollec;
	
	protected $_listProductBlock;
	
  public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
		\Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
		\Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Block\Product\ListProduct $listProduct
    ) {
		$this->categoryCollection = $categoryCollection;
		$this->configurableType = $configurableType;
		$this->_productloader = $_productloader;
        $this->_productCollection = $productCollection;
        $this->_listProduct = $listProduct;
        parent::__construct($context);
    }
	
	public function getCategoryCollection()
	{
		if($this->categories == null) {
			$this->categories = $this->categoryCollection->create()
						->addAttributeToSelect('*')
						->addAttributeToFilter('is_active',1)
						->addAttributeToFilter('include_in_menu', 1)
						->addAttributeToFilter('entity_id', array('neq' => 2));
			$this->categories->getSelect()->orderRand();
		}
		return $this->categories;
	}
	
	public function getPopularCollection()
	{
		if($this->_productColl == null){
			$this->_productColl = $this->_productCollection->create()->addAttributeToSelect("*")
							->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
							->addAttributeToFilter('visibility',\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							->addAttributeToFilter('popular',\Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_YES)
							->setPageSize(10)
							->setCurPage(1);
			$this->_productColl->getSelect()->orderRand();
		}
		return $this->_productColl;
	}
	
	public function getNewCollection()
	{
		if($this->_productCol == null){
			$this->_productCol = $this->_productCollection->create()->addAttributeToSelect("*")
							->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
							->addAttributeToFilter('visibility',\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							->addAttributeToFilter('new_product',\Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_YES)
							->setPageSize(10)
							->setCurPage(1);
			$this->_productCol->getSelect()->orderRand();
		}
		return $this->_productCol;
	}
	
	public function getSpecialCollection()
	{
		if($this->_productCollec == null){
			$this->_productCollec = $this->_productCollection->create()->addAttributeToSelect("*")
							->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
							->addAttributeToFilter('visibility',\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							->addAttributeToFilter('special_product',\Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_YES)
							->setPageSize(10)
							->setCurPage(1);
			$this->_productCollec->getSelect()->orderRand();
		}
		return $this->_productCollec;
	}
	
	public function getImage($_product,$image){
		if($this->_listProductBlock == null){
			$this->_listProductBlock = $this->_listProduct;
		}
		return $this->_listProductBlock->getImage($_product,$image);
	}
	
	public function getProductPrice($_product){
		if($this->_listProductBlock == null){
			$this->_listProductBlock = $this->_listProduct;
		}
		return $this->_listProductBlock->getProductPrice($_product);
	}
	
	public function getAddToCartPostParams($_product){
		if($this->_listProductBlock == null){
			$this->_listProductBlock = $this->_listProduct;
		}
		return $this->_listProductBlock->getAddToCartPostParams($_product);
	}
	
	public function getReviewsSummaryHtml($_product, $templateType){
		if($this->_listProductBlock == null){
			$this->_listProductBlock = $this->_listProduct;
		}
		return $this->_listProductBlock->getReviewsSummaryHtml($_product, $templateType, true);
	}
	
	public function getBackorderProducts(){
		if($this->_backproductCollec == null){
			$this->_backproductCollec = $this->_productCollection->create()->addAttributeToSelect("*")
				->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
				->addAttributeToFilter('type_id', array('in' => array('simple','virtual','Downloadable')))
				->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
				->addAttributeToFilter('qty',['lteq'=>0])
				->setCurPage(1);
			$this->_backproductCollec->getSelect()->orderRand();
		}
		return $this->_backproductCollec;
	}
	
	public function getConfigParentProduct($product_id){
		$parentIds = $this->configurableType->getParentIdsByChild($product_id);
		$parentId = array_shift($parentIds);
		return $parentId;
	}
	
	public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
}
