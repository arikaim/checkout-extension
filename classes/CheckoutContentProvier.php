<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2016-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Extensions\Checkout\Classes;

use Arikaim\Core\Content\Traits\ContentProvider;
use Arikaim\Core\Interfaces\Content\ContentProviderInterface;
use Arikaim\Core\Http\Session;
use Arikaim\Core\Utils\Uuid;

/**
 * CheckoutContentProvier class
 */
class CheckoutContentProvier implements ContentProviderInterface
{
    use 
        ContentProvider;       

    /**
     * Content provider content types
     *
     * @var array
     */
    protected $supportedContentTypes = ['checkout'];

    /**
     * Provider name
     *
     * @var string
     */
    protected $contentProviderName  = 'checkout';
    
    /**
     * Content provider title
     *
     * @var string
     */
    protected $contentProviderTitle = 'Checkout data';

    /**
     * Get total data items
     *
     * @return integer|null
     */
    public function getItemsCount(): ?int
    {
        return null;
    }

    /**
     * Get content
     *
     * @param string|int|array $key  Id, Uuid or content name slug
     * @param string|null $contentType  Content type name
     * @return array|null
     */
    public function getContent($key, ?string $contentType = null): ?array
    {
        return (empty($key) == true) ? null : Session::get($key,null);      
    }

    /**
     * Create new content item
     *
     * @param array $data
     * @param string|null $contentType  Content type name
     * @return array|null
     */
    public function createItem(array $data, ?string $contentType = null): ?array
    {
        $key = $data['key'] ?? $data['id'] ?? null;
        if (empty($key) == true) {
            return null;
        }
        $this->saveItem($key,$data,$contentType);

        return $data;
    }

    /**
     * Save content item
     *
     * @param string|int $key
     * @param array $data
     * @param string|null $contentType  Content type name
     * @return boolean
     */
    public function saveItem($key, array $data, ?string $contentType = null): bool
    {
        $data['id'] = $data['id'] ?? Uuid::create();
        $data['order_id'] = $data['order_id'] ?? $data['token'] ?? $data['id'];
        
        Session::set($key,$data);    

        return true;          
    }
}
