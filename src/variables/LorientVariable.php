<?php
/**
 * Lorient plugin for Craft CMS 3.x
 *
 * Custom functionality for Lorient CMS
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @cole007
 */

namespace ournameismud\lorient\variables;

use ournameismud\lorient\Lorient;
use ournameismud\lorient\records\Samples AS SampleRecord;
use ournameismud\lorient\records\Addresses AS AddressRecord;
use ournameismud\lorient\records\Orders AS OrderRecord;

use Craft;
use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\Matrix;

/**
 * @author    @cole007
 * @package   Lorient
 * @since     0.0.1
 */
class LorientVariable
{
    protected $cartName = 'lorient_cart';
    // Public Methods
    // =========================================================================

    public function getCategories($category)
    {
        $site = Craft::$app->getSites()->getCurrentSite();
        // get all products
        $products = Entry::find()
            ->section('products')
            ->site($site->handle)
            ->relatedTo([
                'targetElement' => $category,
                'field' => 'product_downloads.download_category'
            ])
            ->ids(); 
        $categories = Category::find()
            ->group('products')
            ->relatedTo($products)
            ->all();
        $output = [];
        foreach ($categories AS $productCategory) {
            if ($productCategory->parent) {
                $parent = $productCategory->parent;
                $output[$parent->id] = array(
                    'name' => $parent->title,
                    'slug' => $parent->slug,
                    'id' => $parent->id,
                    'url' => '?c=' . $parent->slug
                ); 
            } else {
                $output[$productCategory->id] = array(
                    'name' => $productCategory->title,
                    'slug' => $productCategory->slug,
                    'id' => $productCategory->id,
                    'url' => '?c=' . $productCategory->slug
                ); 
            }            
        }
        // natcasesort($output);
        return $output;
    }


    // Name: 
    //      getFavourites
    // Purpose: 
    // Context: 
    //      templates/account/index
    // Required: 
    //      none
    // Optional: 
    //      $element (id)
    // Services:
    //      favourites->getFavourites
    // Returns: 
    //      $response (array of element IDs)
    
    public function getFavourites($element = null)
    {
        $favourites = Lorient::getInstance()->favourites->getFavourites( $element );
        $response = array();
        foreach( $favourites AS $favourite) $response[] = $favourite['element'];
        return $response;
    }

    // Name: 
    //      getSamples
    // Purpose: 
    // Context: 
    //      templates/account/index
    // Required: 
    //      none
    // Optional: 
    //      $sampleId (id)
    // Services:
    //      samples->getSamples
    // Returns: 
    //      $response (sample records)
    
    public function getSamples( $sampleId = null )
    {
        $samples = Lorient::getInstance()->samples->getSamples( $sampleId );
        $response = [];
        foreach ($samples AS $sample) {
            $tmpResponse = [];
            $tmpResponse['id'] = $sample->id;
            $tmpResponse['order'] = $sample->order;
            $tmpResponse['element'] = $sample->element;
            $tmpResponse['specs'] = json_decode($sample->specs);
            $response[] = $tmpResponse;
        }
        return $response;
    }

    // Name: 
    //      getAddress
    // Purpose: 
    //      get address record via the user/site
    // Context:
    //      src/templates/order
    //      src/templates/orders
    //      templates/account/confirm
    //      templates/account/order
    // Required: 
    //      none
    // Optional: 
    //      none
    // Services:
    //      $this->getUser
    // Returns: 
    //      $addressRecord (address record)
    
    public function getAddress()
    {
        $userRef = $this->getUser();
        $address = array();
        $site = Craft::$app->getSites()->getCurrentSite();   
        // $getAdd
        $address['siteId'] = $site->id;
        $address['owner'] = $userRef;
        // abstract to service?
        $addressRecord = AddressRecord::find()
            ->where($address)
            ->orderby('dateUpdated DESC')
            ->one();            
        return $addressRecord;
    }
    

    public function addressFields()
    {

        $addressFields = [
            [
                'label'=>'Title',
                'name'=>'title',    
                'type'=>'dropdown',
                'size'=>'small',
                'options'=>['Mr','Mrs','Miss','Dr'],
                'required' => false
            ],
            [
                'label'=>'First Name',
                'name'=>'firstName',    
                'type'=>'text',
                'size'=>'medium',
                'required' => false
            ],
            [
                'label'=>'Surname',
                'name'=>'secondName',    
                'type'=>'text',
                'size'=>'large',
                'required' => true
            ],
            [
                'label'=>'Company',
                'name'=>'company',    
                'type'=>'text',
                'size'=>'large',
                'required' => true
            ],
            [
                'label'=>'Address 1',
                'name'=>'address1',    
                'type'=>'text',
                'size'=>'large',
                'required' => true
            ],
            [
                'label'=>'Address 2',
                'name'=>'address2',    
                'type'=>'text',
                'size'=>'large',
                'required' => false
            ],
            [
                'label'=>'State',
                'name'=>'state',    
                'type'=>'text',
                'size'=>'medium',
                'required' => false
            ],
            [
                'label'=>'Town/City',
                'name'=>'townCity',    
                'type'=>'text',
                'size'=>'medium',
                'required' => true
            ],
            [
                'label'=>'Postcode',
                'name'=>'postcode',    
                'type'=>'text',
                'size'=>'medium',
                'required' => true
            ],
            [
                'label'=>'Telephone',
                'name'=>'telephone',    
                'type'=>'text',
                'size'=>'medium',
                'required' => true
            ],
        ];
        return $addressFields;
    }
    
    // Name: 
    //      getAddress
    // Purpose: 
    //      get address record via the record id
    // Context:
    //      lorient/src/templates/order
    //      lorient/src/templates/orders
    //      templates/account/confirm
    //      templates/account/order
    // Required: 
    //      none
    // Optional: 
    //      none
    // Services:
    //      none
    // Returns: 
    //      $addressRecord (address record)

    public function getAddressById( $id )
    {
        $addressRecord = AddressRecord::find()
            ->where([ 'id' => $id ])->one();
        return $addressRecord;
    }
    
    // Name: 
    //      getUser
    // Purpose: 
    //      get or generate a user reference
    // Context:
    //           
    // Required: 
    //      none
    // Optional: 
    //      none
    // Services:
    //      samples->setCardId
    // Returns: 
    //      $userRef (id or string)

    public function getUser()
    {
        $session = Craft::$app->getSession();
        $cart = $session[$this->cartName];
        $user = Craft::$app->getUser();
        if($user->id == null) {
            if (!$cart) {
                $cart = Lorient::getInstance()->samples->setCartId();
                $session->set($this->cartName, $cart);
            }
            $userRef = $cart;
        } else {
            $userRef = $user->id;
        }
        return $userRef;
    }
    
    // Name: 
    //      getOrders
    // Purpose: 
    //      get orders by logged in user/session
    // Context:
    //      templates/account/index
    // Required: 
    //      none
    // Optional: 
    //      none
    // Services:
    //      $this->getUser
    // Returns: 
    //      $orderRecords (order Records)
         
    public function getOrders( )
    {
        $userRef = $this->getUser();
        // abstract to service?
        $orderRecords = OrderRecord::find()
            ->where( [ 'owner' => $userRef ] )->all();
        return $orderRecords;
    }

    // Name: 
    //      getSavedOrders
    // Purpose: 
    //      get orders by logged in user/session
    // Context:
    //      templates/account/index
    // Required: 
    //      none
    // Optional: 
    //      none
    // Services:
    //      $this->getUser
    // Returns: 
    //      $orderRecords (order Records)
         
    public function getSavedOrders( $sort = 'dateUpdated DESC' )
    {
        $userRef = $this->getUser();
        // abstract to service?
        
        $orders = [];
        $orderRecords = OrderRecord::find()
            ->where( [ 'owner' => $userRef ] )
            ->orderBy( $sort )  
            ->all();
        foreach ($orderRecords AS $order) {
            $orderItems = [];
            $orderProducts = SampleRecord::find()
                ->where( [ 'owner' => $userRef, 'order' => $order->id])
                ->all();
            foreach ($orderProducts AS $product) {
                $orderItems[] = $product->element;
            }
            $orders[] = array(
                'ref' => $order->projectRef,
                'id' => $order->id,
                'status' => $order->status,
                'date' => $order->dateUpdated,
                'elements' => $orderItems,
            );
        }
        
        return $orders;
    }
    
    // Name: 
    //      getOrdersAll
    // Purpose: 
    //      get all orders or orders by defined criteria
    // Context:
    //      lorient/src/templates/orders
    // Required: 
    //      none
    // Optional: 
    //      $crit
    // Services:
    //      none
    // Returns: 
    //      $orderRecords (order Records)
         
    public function getOrdersAll( $crit = null )
    {
        $sort = array_key_exists('sort',$crit) ? $crit['sort'] : 'id'; 
        $by = array_key_exists('by',$crit) ? $crit['by'] : 'asc'; 
        $by = ($by == 'asc') ? SORT_ASC : SORT_DESC;
        
        $orderRecords = OrderRecord::find()->orderBy([$sort => $by]);
        $crit = array_filter($crit);
        // if (array_key_exists('limit',$crit)) $orderRecords->limit($crit['limit']);
        // if (array_key_exists('offset',$crit)) $orderRecords->offset($crit['offset']);
        if (array_key_exists('key',$crit) && array_key_exists('value',$crit)) $orderRecords->where([$crit['key'] => $crit['value']]);
        // if (array_key_exists('offset',$crit)) $orderRecords->offset($crit['offset']);
        
        return $orderRecords->all();
    }
    
    // Name: 
    //      getOrderByHash
    // Purpose: 
    //      get a specific order by its defined hash
    // Context:
    //      lorient/src/templates/order
    //      templates/account/confirm
    //      templates/account/order
    // Required: 
    //      $hash (string)
    // Optional: 
    //      none
    // Services:
    //      none
    // Returns: 
    //      $orderRecord (order Record)
         
    public function getOrderByHash( $hash )
    {
        $id = Craft::$app->security->validateData( $hash );
        // abstract to service?
        $orderRecord = OrderRecord::find()
            ->where( [ 'id' => $id ] )->one();            
        return $orderRecord;    
    }

    // Name: 
    //      getCart
    // Purpose: 
    //      get a specific cart by its id
    // Context:
    //      templates/account/cart
    //      templates/account/confirm
    //      templates/account/order
    // Required: 
    //      $id (integer)
    // Optional: 
    //      none
    // Services:
    //      samples->getCart
    //      $this->getUser
    // Returns: 
    //      $response (array of element IDs)
         
    public function getCart($id = null)
    {
        $site = Craft::$app->getSites()->getCurrentSite();

        $userRef = $this->getUser();
        // abstract to service?
        $cart = Lorient::getInstance()->samples->getCart( $userRef, $id );
        $response = array();
        foreach( $cart AS $item) $response[] = $item['element'];

        $products = Entry::find()
            ->site($site->handle)
            ->id($response)
            ->ids(); 
        
        return $products;
    }
    
    // Name: 
    //      getCart
    // Purpose: 
    //      get a specific cart by its id
    // Context:
    //      templates/account/cart
    //      templates/account/confirm
    //      templates/account/order
    // Required: 
    //      $id (integer)
    // Optional: 
    //      none
    // Services:
    //      samples->getCart
    //      $this->getUser
    // Returns: 
    //      $response (array of element IDs)
         
    public function getCartFull($id = null)
    {
        $site = Craft::$app->getSites()->getCurrentSite();

        $userRef = $this->getUser();
        // abstract to service?
        $cart = Lorient::getInstance()->samples->getCart( $userRef, $id );
        $response = array();
        $ids = array();
        foreach( $cart AS $item) {
            $response[$item['element']] = $item;
            $ids[] = $item['element'];
        }

        $products = Entry::find()
            ->site($site->handle)
            ->id($ids)
            ->ids(); 

        $output = array();
        foreach ($products AS $productId) {
            $output[] = $response[$productId];
        }    
        return $output;
    }

    // Name: 
    //      getCartById
    // Purpose: 
    //      get a specific cart by its order reference
    // Context:
    //      lorient/src/templates/order
    //      lorient/src/templates/orders
    //      templates/account/index
    // Required: 
    //      $id (integer)
    // Optional: 
    //      none
    // Services:
    //      samples->getCartByOrder
    // Returns: 
    //      $response (array of element IDs)
         
    public function getCartById( $id )
    {
        $site = Craft::$app->getSites()->getCurrentSite();
        // $userRef = $this->getUser();
        // // abstract to service?
        $cart = Lorient::getInstance()->samples->getCartByOrder( $id );
        $response = array();
        foreach( $cart AS $item) $response[] = $item['element'];
        $products = Entry::find()
            ->site($site->handle)
            ->id($response)
            ->ids(); 
        
        return $products;
    }

    public function buildPagination( $current, $limit, $total, $url, $criteria = null, $prefix = 'p', $sort = null)
    {
        $prevUrl = null;
        $prevUrls = [];
        $nextUrl = null;
        $nextUrls = [];
        if ($criteria) {
            $tmp = [];
            foreach ($criteria AS $key => $value) {
                if (is_array($value)) {
                    foreach ($value AS $arrKey => $arrVal) {
                        $tmp[] = $key . '[]=' . $arrVal; 
                    }
                } else {
                    $tmp[] = $key . '=' .$value; 
                }
            }
            $url .= '?' . implode('&',$tmp) . '&';            
        }
        else $url .= '?';

        $baseUrl = $url;
        if ($sort) $url .= 'sort='. $sort . '&';

        if ($current > 1) {
            $prevUrl = $url . $prefix . '=' . ($current - 1);
            $pStart = $current - 3;
            $pEnd = $current - 1;
            for ($i = $pStart; $i <= $pEnd; $i++) {
                if($i > 0) $prevUrls[$i] = $url . $prefix . '=' . $i;
            }            
        }
        if ($current < $total) {
            $nextUrl = $url . $prefix . '=' . ($current + 1);
            $nStart = $current + 1;
            $nEnd = $current + 3;
            for ($i = $nStart; $i <= $nEnd; $i++) {
                if($i <= $total) $nextUrls[$i] = $url . $prefix . '=' . $i;
            }               
        }        

        $pageInfo = array(
            'prevUrl' => $prevUrl,
            'getPrevUrls' => $prevUrls,
            'baseUrl' => $baseUrl,
            'currentPage' => (int)$current,
            'nextUrl' => $nextUrl,
            'getNextUrls' => $nextUrls            
        );

        return $pageInfo;
    }
}
