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

use Arikaim\Modules\Checkout\Interfaces\TransactionStorageInterface;
use Arikaim\Modules\Checkout\Interfaces\TransactionInterface;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\UserRelation;
use Arikaim\Core\Db\Traits\Status;

/**
 * Checkout transactions model class
 */
class Transactions extends Model implements TransactionStorageInterface
{
    use Uuid,
        Status,
        UserRelation,
        Find;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'amount',
        'currency',
        'checkout_driver',
        'payer',
        'status',
        'details',
        'user_id',
        'date_created'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * full_details attribute
     *
     * @return mixed
     */
    public function getFullDetailsAttribute()
    {
        return (empty($this->details) == true) ? null : \json_decode($this->details,true);
    }

    /**
     * Save transaction
     *
     * @param TransactionInterface $transaction
     * @param int|null $userId
     * @return boolean
     */
    public function saveTransaction(TransactionInterface $transaction, ?int $userId = null): bool
    {
        $saved = $this->getTransaction($transaction->getTransactionId());
        if (\is_object($saved) == true) {
            return (bool)$saved->update([
               'status' => $transaction->getStatus()
            ]);
        }

        $info = [
            'transaction_id'  => $transaction->getTransactionId(),
            'amount'          => $transaction->getAmount(),
            'currency'        => $transaction->getCurrency(),
            'checkout_driver' => $transaction->getCheckoutDriver(),
            'status'          => $transaction->getStatus(),
            'payer'           => $transaction->getPayerEmail(),
            'date_created'    => $transaction->getDateTimeCreated(),
            'details'         => \json_encode($transaction->getDetails()),
            'user_id'         => (empty($userId) == true) ? null : $userId 
        ];

        $model = $this->create($info);

        return \is_object($model);
    }

    /**
     * Get transaction
     *
     * @param string $id
     * @return Model|null
    */
    public function getTransaction($id)
    {
        return $this->where('transaction_id','=',$id)->first();       
    }
}
