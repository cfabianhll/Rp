<?php

namespace Emizentech\Countdown\Block;


class Countdown extends \Magento\Framework\View\Element\Template
{
	
	protected $_productColl;
	
	protected $_listProductBlock;
	
  public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
		\Magento\Framework\Pricing\Helper\Data $priceformat,
		\Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Block\Product\ListProduct $listProduct
    ) {
		$this->_productloader = $_productloader;
		$this->_priceformat = $priceformat;
        $this->_productCollection = $productCollection;
        $this->_listProduct = $listProduct;
        parent::__construct($context);
    }
	
	
	public function getDailyDealProducts()
	{
		if($this->_productColl == null){
			$this->_productColl = $this->_productCollection->create()->addAttributeToSelect("*")
								->addAttributeToFilter('countdown_enabled', ['eq'=>'1'])	
								->addAttributeToFilter('special_price', ['gt'=>0])								
								->setCurPage(1);
			$this->_productColl->getSelect()->orderRand();
		}
		return $this->_productColl;
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
	
	public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
	
	public function getPriceFormat($price)
    {
        return $this->_priceformat->currency($price, true, false);
    }

    public function isEnabled(){
        return $this->_scopeConfig->getValue('countdown/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
