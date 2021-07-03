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
 * Transactions db table
 */
class TransactionsSchema extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = "transactions";

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
        $table->userId();
        $table->status();
        $table->string('transaction_id')->nullable(false);
        $table->decimal('amount',15,4)->nullable(false);
        $table->string('currency')->nullable(false);
        $table->string('checkout_driver')->nullable(false);    
        $table->string('payer')->nullable(false);
        $table->dateCreated();
        $table->text('details')->nullable(true);
        // index
        $table->unique(['transaction_id']);
        $table->index('payer');  
        $table->index('checkout_driver');  
        $table->index('amount');          
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
