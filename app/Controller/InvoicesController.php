<?php

/*
 * Controller for Invoice model
 *
 * @package       app.Controller
 * 
 */

class InvoicesController extends AppController {

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Team');
        $this->loadModel('Plan');
        $this->loadModel('Management');
        $this->loadModel('AccountUser');
        $this->loadModel('AccessLog');
        $this->loadModel('Tag');
        $this->loadModel('Bookmark');
    }

    /**
     * Show list invoice
     */
    public function index() {
        $conditions = array();
        $cr_year = date('Y');
        if ($this->request->query) {
            $cr_year = $this->request->query['year'];
            $conditions = array(
                'regist_date LIKE' => "%$cr_year%"
            );
        }
       
        $this->Invoice->recursive = -1;
        $this->paginate = array(
            'conditions' => $conditions,
            'order' => array('id' => 'asc'),
        );
        $invoices = $this->paginate('Invoice');
        if ($invoices) {
            $invoice['team_id'] = $invoice['tag'] = $invoice['contents'] = null;
            foreach ($invoices as &$invoice) {
                $id = $invoice['Invoice']['id'];
                $invoice_datas = $this->Invoice->InvoiceBreakdown->find('all', array(
                    'conditions' => array(
                        'invoice_id' => $id
                    )
                ));
                if ($invoice_datas) {
                    foreach ($invoice_datas as $invoice_data) {
                        $invoice['Invoice']['team_id'][] = $invoice_data['InvoiceBreakdown']['team_id'];
                        $invoice['Invoice']['tag'][] = $invoice_data['InvoiceBreakdown']['tag'];
                        $invoice['Invoice']['contents'][] = $invoice_data['InvoiceBreakdown']['contents'];
                    }
                }
            }
        }
        $this->set(array(
            'invoices' => $invoices,
            'cr_year' => $cr_year
        ));
    }

    /**
     * Show invoice's detail
     * $param int $id invoice's id
     */
    public function detail($id) {
        $this->_setModalLayout();
        if (!$id) {
            $this->Session->setFlash(__('請求書のIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Invoice->recursive = -1;
        $invoice = $this->Invoice->findById($id);
        if (!$invoice) {
            $this->Session->setFlash(__('請求書のIDは無効です。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        $invoiceDatas = $this->Invoice->InvoiceBreakdown->find('all', array(
            'conditions' => array(
                'invoice_id' => $id
            ),
            'fields' => array('invoice_id', 'team_id', 'tag', 'contents')
        ));
        if ($invoiceDatas) {
            $invoice['Invoice']['team_id'] = $invoice['Invoice']['tag'] = $invoice['Invoice']['contents'] = null;
            foreach ($invoiceDatas as $data) {
                $invoice['Invoice']['team_id'][] = $data['InvoiceBreakdown']['team_id'];
                $invoice['Invoice']['tag'][] = $data['InvoiceBreakdown']['tag'];
                $invoice['Invoice']['contents'][] = $data['InvoiceBreakdown']['contents'];
            }
        }

        $this->set(array(
            'invoice' => $invoice['Invoice']
        ));
    }

    /**
     * Build a URL will all the search elements in it
     */
    function filter() {
        // the page we will redirect to
        $url['action'] = 'index';

        // build a URL will all the search elements in it
        // the resulting URL will be
        // example.com/cake/posts/index/Search.keywords:mykeyword/Search.tag_id:3
        foreach ($this->data as $k => $v) {
            foreach ($v as $kk => $vv) {
                $url[$k . '.' . $kk] = $vv;
            }
        }

        // redirect the user to the url
        $this->redirect($url, null, true);
    }

    /**
     * Show list invoice for system view
     */
    public function system_index() {
        /* Show last login date */
        $this->loadModel('Management');
        if ($this->Auth->user()) {
            $user_id = $this->Auth->user('id');
            $last_login = $this->Management->find('first', array(
                'conditions' => array('id' => $user_id),
                'fields' => array('last_login_date')
            ));
        }

        // Filter
        $cr_year = date('Y');
        $conditions = array();
        //Transform POST into GET
        if (($this->request->is('post') || $this->request->is('put')) && isset($this->data['Filter'])) {
            $filter_url['controller'] = $this->request->params['controller'];
            $filter_url['action'] = $this->request->params['action'];
            // We need to overwrite the page every time we change the parameters
            $filter_url['page'] = 1;

            // for each filter we will add a GET parameter for the generated url
            if (isset($this->data['Filter'])) {
                foreach ($this->data['Filter'] as $name => $value) {
                    if ($value != trim('')) {
                        // You might want to sanitize the $value here
                        // or even do a urlencode to be sure
                        $filter_url[$name] = Utility_Str::escapehtml($value);
                    }
                }
            }
            // now that we have generated an url with GET parameters, 
            // we'll redirect to that page
            return $this->redirect($filter_url);
        } else {
            // Inspect all the named parameters to apply the filters            
            foreach ($this->params['named'] as $param_name => $value) {
                $value = Utility_Str::returnhtml($value);
                // Don't apply the default named parameters used for pagination
                if (!in_array($param_name, array('page', 'sort', 'direction', 'limit'))) {
                    // You may use a switch here to make special filters
                    // like "between dates", "greater than", etc
                    if ($param_name == "name") {
                        $conditions['OR'] = array(
                            array('AccountUser.family_name LIKE' => '%' . str_replace('%', '\%', $value) . '%'),
                            array('AccountUser.company LIKE' => '%' . str_replace('%', '\%', $value) . '%')
                        );
                    } elseif ($param_name == "year") {
                        $cr_year = $value;
                        if (!isset($this->params['named']['month'])) {
                            $conditions += array(
                                'Invoice.regist_date LIKE' => "%$cr_year%"
                            );
                        }
                    } elseif ($param_name == "month") {
                        $value = ($value < 10) ? "0$value" : $value;
                        if (isset($this->params['named']['year'])) {
                            $conditions += array(
                                'Invoice.regist_date LIKE' => "%$cr_year-$value%"
                            );
                        } else {
                            $conditions += array(
                                'MONTH(Invoice.regist_date)' => $value
                            );
                        }
                    } elseif ($param_name == "status") {
                        $conditions += array(
                            'Invoice.status' => $value
                        );
                    } else {
                        $conditions += array('AccountUser.country' => $value);
                    }

                    $this->request->data['Filter'][$param_name] = $value;
                }
            }
        }
        $this->Invoice->recursive = 0;
        $this->paginate = array(
            'conditions' => $conditions,
            'limit' => 20,
            'order' => array('id' => 'asc'),
        );
        $invoices = $this->paginate('Invoice');
        if ($invoices) {
            $invoice['team_id'] = $invoice['tag'] = $invoice['contents'] = null;
            foreach ($invoices as &$invoice) {
                $id = $invoice['Invoice']['id'];
                $invoice_datas = $this->Invoice->InvoiceBreakdown->find('all', array(
                    'conditions' => array(
                        'invoice_id' => $id
                    )
                ));
                if ($invoice_datas) {
                    foreach ($invoice_datas as $invoice_data) {
                        $invoice['Invoice']['team_id'][] = $invoice_data['InvoiceBreakdown']['team_id'];
                        $invoice['Invoice']['tag'][] = $invoice_data['InvoiceBreakdown']['tag'];
                        $invoice['Invoice']['contents'][] = $invoice_data['InvoiceBreakdown']['contents'];
                    }
                }
            }
        }
        $this->set(array(
            'invoices' => $invoices,
            'cr_year' => $cr_year,
            'last_login' => $last_login
        ));
    }

    /**
     * Show invoice's detail in system view
     * $param int $id invoice's id
     */
    public function system_detail($id) {
        $this->_setModalLayout();
        if (!$id) {
            $this->Session->setFlash(__('請求書のIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Invoice->recursive = 0;
        $invoice = $this->Invoice->findById($id);
        if (!$invoice) {
            $this->Session->setFlash(__('請求書のIDは無効です。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        $invoiceDatas = $this->Invoice->InvoiceBreakdown->find('all', array(
            'conditions' => array(
                'invoice_id' => $id
            ),
            'fields' => array('invoice_id', 'team_id', 'tag', 'contents')
        ));
        if ($invoiceDatas) {
            $invoice['Invoice']['team_id'] = $invoice['Invoice']['tag'] = $invoice['Invoice']['contents'] = null;
            foreach ($invoiceDatas as $data) {
                $invoice['Invoice']['team_id'][] = $data['InvoiceBreakdown']['team_id'];
                $invoice['Invoice']['tag'][] = $data['InvoiceBreakdown']['tag'];
                $invoice['Invoice']['contents'][] = $data['InvoiceBreakdown']['contents'];
            }
        }

        $this->set(array(
            'invoice' => $invoice['Invoice'],
            'accountUser' => $invoice['AccountUser']
        ));
    }

    /**
     * Change Invoice status
     * @return json
     */
    public function changeStatus() {
        if ($this->request->is('post')) {
            $id = $this->request->data['id'];
            $status = $this->request->data['status'];
            $status = ($status == 1) ? 2 : 3;
            $this->Invoice->id = $id;
            if ($this->Invoice->save(array('status' => $status))) {
                $this->Session->setFlash(__('Change Status susscess。'), 'alert-box', array('class' => 'alert-success'));
            } else {
                $this->Session->setFlash(__('Change Status fail。'), 'alert-box', array('class' => 'alert-danger'));
            }
            echo json_encode(1);
            exit;
        }
    }

    /**
     * Change Invoice status for system
     * @return json
     */
    public function system_changeStatus() {
        $this->changeStatus();
    }

}

?>
