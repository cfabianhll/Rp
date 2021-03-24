<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Trootech\General\Block\Html;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Category;



/**
 * Html page top menu block
 *
 * @api
 * @since 100.0.2
 */
class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
    

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var TreeFactory
     */
    private $treeFactory;

    protected $_categoryCollection;

    private $category;

    public function __construct(
     \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory  $categoryCollection,
	 \Magento\Framework\App\Request\Http $request,
     Template\Context $context,
     NodeFactory $nodeFactory,
     TreeFactory $treeFactory,
     Category $category,
     array $data = []
)
{
    
    parent::__construct($context, $nodeFactory, $treeFactory, $data);
    $this->_categoryCollection = $categoryCollection;
	$this->_request = $request;
    $this->category = $category;
}

    
    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
	public function getFullActionName()
	{
		return $this->_request->getFullActionName();
	}
	
	protected function getCacheLifetime()
    {
        return 0;
    }
	
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->getMenu(), 'block' => $this, 'request' => $this->getRequest()]
        );

        $this->getMenu()->setOutermostClass($outermostClass);
        $this->getMenu()->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->getMenu(), $childrenWrapClass, $limit);

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->getMenu(), 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }

    

    
    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = [];
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }
		
		if($child->getLevel() == 0) {

			$html .= '<div class="sub_mega_menu" id="link_1"><div class="main_mega_menu"><div class="listing_of_products">';

		} /*else {	
			$html .= '<ul class="col-12 col-md-3 left_mega_menu_Sub">';
		}*/
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
		if($child->getLevel() == 0) {

			$html .= '</div>';
            $collection = $this->_categoryCollection->create();
            $collection->addFieldToFilter('name',$child->getName());
            //if ($collection->getSize()) {
                $category = $this->category->load($collection->getFirstItem()->getId());
                /*print_r($catobj->getData());
                print_r($catobj->getCustomImage());*/
          // }
//print_r($category->getData());
                if($category->getImageUrl('custom_image')) {
           $html .=  '<div class="img_advertise"><a href="' . $category->getUrl() . '"><img src="' .$category->getImageUrl('custom_image'). '"></a>';  

           $html .= '</div>';
       }
          $html .= '</div></div>';


		}/* else {	
			$html .= '</ul>';
		}
        */

        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param \Magento\Framework\Data\Tree\Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;
        $count=0;
        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'menu-';

        /** @var \Magento\Framework\Data\Tree\Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                continue;
            }
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
             if($counter%3 == 0) {
            $child->setIsLast($counter);
            }
            $child->setPositionClass($itemPositionClassPrefix . $counter);
           /* echo '<pre>';
            print_r($child->getData());*/

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $currentClass = $child->getClass();

                if (empty($currentClass)) {
                    $child->setClass($outermostClass);
                } else {
                    $child->setClass($currentClass . ' ' . $outermostClass);
                }
            }

            /*if (is_array($colBrakes) && count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }*/

            if ($childLevel == 1 ){
                  $html .= '<div class="category_divs">';
                  $html .= '<div class="row">';
            }

			if($child->getLevel() == 0) {
				$html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
			} 

           else if($child->getLevel() == 1) {
                /*$html .= '<div class="category_divs">';
                $html .= '<div class="row">';*/
				$html .= '<div class="col-12 col-md-3 left_mega_menu_Sub text-left">';
			} 
            else if($child->getLevel() == 2) {
                /*$html .= '<div class="category_divs">';
                $html .= '<div class="row">';*/
                   if($count%35==0){
                          $html .= '<ul data-test-id="test'.$child->getLevel().'" class="left_mega_menu_Sub">';

                        }

                 $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                 /*if($counter == 4){
                break; //break it at 3 for children
             }*/
             $count++;
            } 
    //         else if($child->getLevel() > 2){ 
				// $html .= '<ul class="third-cat left_mega_menu_Sub">';
				//  $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
    //         }
   //           else {
   //          $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
			// }
            if($child->getLevel() == 1) {

                        $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><h5>' . $this->escapeHtml(
                            $child->getName()
                        ) . '</h5></a>';
                }
                else if($child->getLevel() <= 2)
                {
                        $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                            $child->getName()
                        ) . '</span></a>';
                }

            if($child->getLevel() == 1) {
                  if($child->hasChildren()){
                      $html .= '<a href="' . $child->getUrl() . '" class="see_all">'.$this->escapeHtml(__('See All')).'</a>';
                  }
             $html .= '</div>';
             $html .= '<div class="col-md-9">';
            }

			if($child->getLevel() == 0 && $child->hasChildren()) {
				$html .= '<i class="fas fa-chevron-right"></i>';
			}
			$html .= $this->_addSubMenu(
                $child,
                $childLevel,
                $childrenWrapClass,
                $limit
            );
            
            

			if($child->getLevel() == 0) {
			$html .= '</li>';	
			} 
            else if($child->getLevel() == 2) {
                $html .= '</li>';
                if($count%35==0){
                    $html .= '</ul>';
                }
                

            }
            /*else if($child->getLevel() == 1) {
                    $html .= '</div>';
                
            }*/
            // else if($child->getLevel() > 2) {
            //     $html .= '</li>';
               
            //         $html .= '</ul>';
                

            // }
            else if($child->getLevel() == 1) {
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            } 
            else {
			$html .= '</li>';
			}

            $itemPosition++;
            $counter++;
        }

        if (is_array($colBrakes) && count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    

    /**
     * Returns array of menu item's attributes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        return ['class' => implode(' ', $menuItemClasses)];
    }

    /**
     * Returns array of menu item's classes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];

        $classes[] = 'level' . $item->getLevel();
		/* if($item->getLevel() == 1){
			$classes[] = 'col-md-3';
		} else if($item->getLevel() == 0) {
			$classes[] = 'nav-item';
		} */
        $classes[] = $item->getPositionClass();

        if($item->getLevel() >= 1){
            $classes[] = 'menu_1';
        }

        if ($item->getIsCategory()) {
            $classes[] = 'category-item';
        }

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        } elseif ($item->getHasActive()) {
            $classes[] = 'has-active active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren() && $item->getLevel() < 1) {
            $classes[] = 'parent';
        }

        return $classes;
    }
}
