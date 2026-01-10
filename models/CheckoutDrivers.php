<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Checkout\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\DefaultTrait;

/**
 * Checkout drivers model class
 */
class CheckoutDrivers extends Model 
{
    use Uuid,
        Status,
        DefaultTrait,
        Find;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'checkout_drivers';

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'default',
        'status',
        'driver_name',
        'title',
        'category',
        'status',
        'crypto'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Defautl trait key column
     *
     * @var string
     */
    protected $defaultKeyColumnName = 'category';

    /**
     * Drivers scope query
     *
     * @param Builder      $query
     * @param string|null  $category
     * @param integer|null $status
     * @param integer|null $crypto
     * @return Builder
     */
    public function scopeDriversQuery($query, ?string $category = null, ?int $status = null, ?int $crypto = null)
    {
        if (empty($category) == false) {
            $query->where('category','=',$category);
        }
        if (empty($status) == false) {
            $query->where('status','=',$status);
        }
        if (empty($crypto) == false) {
            $query->where('crypto','=',$crypto);
        }

        return $query;
    }

    /**
     * Scopen find driver
     *
     * @param Builder     $query
     * @param string      $name
     * @param string|null $category
     * @return Builder
     */
    public function scopeFindDriverQuery($query, string $name, ?string $category = null) 
    {
        $query->where('driver_name','=',$name);

        return ($category == null) ? $query->whereNull('category') : $query->where('category','=',$category);        
    }   

    /**
     * Save driver
     *
     * @param string      $name
     * @param string|null $title
     * @param string|null $category
     * @return boolean
     */
    public function saveDriver(string $name, ?string $title = null, ?string $category = null): bool
    {
        $driver = $this->findDriverQuery($name,$category)->first();
        $data = [
            'driver_name' => $name,
            'title'       => $title,
            'category'    => $category
        ];
        
        return ($driver == null) ? ($this->create($data) != null) : ($this->update($data) !== false);         
    }

    /**
     * Update drivers list
     *
     * @param string|null $category
     * @param string      $systemDriversCategory
     * @return boolean
     */
    public function updateDriversList(?string $category = null, string $systemDriversCategory = 'checkout'): bool
    {        
        $drivers = DbModel::Drivers();
        if ($drivers == null) {
            return false;
        }

        $list = $drivers->getDriversList($systemDriversCategory);
        foreach ($list as $item) {
            $this->saveDriver($item['name'],$item['title'],$category);
        }

        return true;
    }
}
