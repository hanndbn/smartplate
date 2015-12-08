<?php

/*
 * Controller for System model
 *
 * @package       app.Controller
 * 
 */

class SystemsController extends AppController {

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();        
        $this->Auth->allow();
    }
        
    
    public function system_index()
    {
        return $this->redirect(array('controller' => 'systems', 'action' => 'login'));
    }

    /**
     * Loggin user
     */
    

}

?>
