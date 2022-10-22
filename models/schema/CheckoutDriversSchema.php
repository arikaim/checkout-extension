<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Checkout\Models\Schema;

use Arikaim\Core\Db\Schema;

/**
 * Checkout drivers data db table
 */
class CheckoutDriversSchema extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = 'checkout_drivers';

    /**
     * Create table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function create($table) 
    {            
        // columns    
        $table->id();
        $table->prototype('uuid'); 
        $table->status();
        $table->string('driver_name')->nullable(false);
        $table->string('category')->nullable(true);
        $table->inrteger('crypto')->nullable(true);
        $table->text('options')->nullable(true);  
        // index            
        $tabele->unique(['driver_name','category']);
    }

    /**
     * Update table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function update($table) 
    {       
    }
}
