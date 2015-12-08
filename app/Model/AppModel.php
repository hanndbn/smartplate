<?php

/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

const REGULAR_EXPRESSION_MAIL_ADDRESS = '/^[\.a-z0-9!#$%&\'*+\/=?^_`{|}~-]+@[-a-z0-9]+(\.[-a-z0-9]+)*\.[a-z]{2,6}$/i';

    /**
     * Array result of cakephp query is wrap by Model's name
     * This function remove that wrap
     * 
     * @param string $wrapper key we want to remove, this usually is Model's name
     * @param array $data raw data return from Model query
     * @param string $key The 'key' parameter provides the column name with which to key the result. 
     * For example, calling removeArrayWrapper('Label', $data, 'id')
     * would result in an array keyed by id:
     * [id] => array('id' => $id, 'label' => $label_name)
     */
    public function removeArrayWrapper($wrapper, array $data, $key = null) {
        $output = array();
        foreach ($data as $value) {
            if ($key || isset($value[$wrapper][$key])) {
                $output[$value[$wrapper][$key]] = $value[$wrapper];
            } else {
                $output[] = $value[$wrapper];
            }
        }

        return $output;
    }

    /**
     * convert DateString from Asia/Tokyo to UTC
     * 
     * @param   $date  date string 'Y-M-D H:i:s'  
     * @return  string 'Tue, 31 Mar 2015 07:11:13 +0000'
     */
    public function convertDateTokyo2UTC($date) {
        $t = new DateTime($date, new DateTimeZone('Asia/Tokyo'));
        $t->setTimeZone(new DateTimeZone('UTC'));
        return $t->format('r');
    }

    /**
     * Get user information in Model
     * @return  array
     */
    function getCurrentUser()
    {
        App::uses('CakeSession', 'Model/Datasource');
        $Session = new CakeSession();

        $user = $Session->read('Auth.User');

        return $user;
    }


}
