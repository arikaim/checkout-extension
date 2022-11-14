<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Checkout\Service;

use Arikaim\Core\Service\Service;
use Arikaim\Core\Service\ServiceInterface;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;

/**
 * Checkout service class
*/
class CheckoutService extends Service implements ServiceInterface
{
    /**
     * Boot service
     *
     * @return void
     */
    public function boot()
    {
        $this->setServiceName('checkout');
    }

    /**
     * Get checkut url path
     *
     * @param string      $driverName
     * @param string      $orderId
     * @param string|null $extension
     * @param string|null $options
     * @return string
     */
    public function getCheckoutUrlPath(
        string $driverName, 
        string $orderId, 
        ?string $extension, 
        ?string $options = null
    ): string
    {
        $extension = $extension ?? 'all';
        $options = (empty($options) == false) ? '/' . $options : '';

        return '/checkout/' . $driverName . '/' . $orderId . '/' . $extension . $options;
    }

    /**
     * Import customer
     *
     * @param mixed $transaction
     * @param int|null $userId
     * @return Model|null
     */
    public function importCustomer($transaction, ?int $userId = null): ?object
    {
        $details = $this->getTransactionDetails($transaction);
        if ($details == null) {
            return null;
        }

        if (empty($userId) == false) {
            $details['user_id'] = $userId;
        }

        // import address
        $address = $this->importAddress($details);
        // import customer
        $driver = Arikaim::driver()->create($details['checkout_driver']);
        $action = $driver->getImportCustomerAction();

        $customer = Arikaim::content()->runAction('entity',$action ?? 'entity.import.stripe',$details);
        if (\is_object($customer) == false) {
            return null;
        }
        if (\is_object($address) == true) {
            // link address
            $customer->address()->linkAddress('home',$address->id);
        }
        
        return $customer;
    }

    /**
     * Import address from checkout transaction
     *
     * @param mixed $transaction
     * @param int|null $userId
     * @return Model|null
     */
    public function importAddress($transaction, ?int $userId = null)
    {
        $details = $this->getTransactionDetails($transaction);
        if ($details == null) {
            return null;
        }
        if (empty($userId) == false) {
            $details['user_id'] = $userId;
        }

        $driver = Arikaim::driver()->create($details['checkout_driver']);
        $action = $driver->getImportCustomerAddressAction();

        return Arikaim::content()->runAction('address',$action ?? 'address.import.stripe',$details);
    }

    /**
     * Get transaction details
     *
     * @param mixed $transaction
     * @return array|null
     */
    protected function getTransactionDetails($transaction): ?array
    {
        if (\is_object($transaction) == false) {
            $transaction = Model::Transactions('checkout')->getTransaction($transaction);
            if (\is_object($transaction) == false) {
                return null;
            }
        }
        
        if (\is_array($transaction->full_details) == false) {
            return null;

        }
        $details = $transaction->full_details;
        $details['checkout_driver'] = $transaction->checkout_driver;
        $details['user_id'] = $transaction->user_id;

        return $details;
    }
}
