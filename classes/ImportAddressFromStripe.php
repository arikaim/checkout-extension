<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Checkout\Classes;

use Arikaim\Core\Content\Type\Action;
use Arikaim\Core\Db\Model;

/**
 * Address import form stripe checkout transaction data
 */
class ImportAddressFromStripe extends Action
{
    /**
     * Init action
     *
     * @return void
     */
    public function init(): void
    {
        $this->setName('stripe.address.import');
        $this->setType('import');
        $this->setTitle('Import address from stripe transaction data.');
    }

    /**
     * Execute action
     *
     * @param ContentItemInterface $content    
     * @param array|null $options
     * @return mixed
     */
    public function execute($content, ?array $options = []) 
    {
        if (empty($content->country_id) == false) {
            $country = Model::Country('address')->findById($content->country_id);
            $countryName = (\is_object($country) == true) ? $country->name : null;          
            $content->setValue('country',$countryName);
        }
        if (empty($content->city_id) == false && empty($content->custom_city) == true) {
            $city = Model::City('address')->findById($content->city_id);
            $cityName = (\is_object($city) == true) ? $city->name : null;           
            $content->setValue('city',$cityName);
        }
        if (empty($content->state_id) == false) {
            $state = Model::State('address')->findById($content->state_id);
            $stateName = (\is_object($state) == true) ? $state->name : null;
            $content->setValue('state',$stateName);
        }
        if (empty($content->custom_city) == false) {
            $content->setValue('city',$content->custom_city);
        }

        return $content;
    }
}
