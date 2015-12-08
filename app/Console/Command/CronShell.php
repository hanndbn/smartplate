<?php

class CronShell extends AppShell {
    public $uses = array('Invoice', 'Team', 'Plan', 'Management', 'AccountUser', 'AccessLog', 'Tag', 'Bookmark');
    
    public function main() {
        // Get all previous month Project
        $previousMonthStart = date('Y-m-01', strtotime('- 1 month'));
        $previousMonthEnd = date('Y-m-t', strtotime('- 1 month'));
        $projects = $this->Team->find('all', array(
            'conditions' => array(
                'cdate BETWEEN ? AND ?' => array($previousMonthStart, $previousMonthEnd)
            ),
            'fields' => array('plan', 'id', 'payment_type')
        ));
        // Get account user id
        if (!empty($projects)) {
            foreach ($projects as $project) {
                $pID = $project['Team']['id'];
                $pPlan = $project['Team']['plan'];
                $paymentType = $project['Team']['payment_type'];
                $accountID = $this->getAccountUser($pID);
                // Get plate count
                $plateCount = $this->Tag->find('count', array(
                    'conditions' => array(
                        'team_id' => $pID
                    )
                ));
                // Get Invoice price
                $price = '';
                if ($pPlan == 1) {
                    // Get tap count
                    $tapCount = $this->AccessLog->platinum_query($pID, 1);
                    $tapCount = array_sum($tapCount);
                    // Get bookmark count
                    $bookmarkCount = $this->Bookmark->find('count', array(
                        'conditions' => array(
                            'team_id' => $pID
                        )
                    ));
                    $price = $plateCount * 100 + $bookmarkCount * 100 + $tapCount * 1;
                } else {
                    $planData = $this->Plan->find('first', array(
                        'conditions' => array(
                            'id' => $pPlan
                        )
                    ));
                    if ($paymentType == 1) { // pay at month
                        $price = $planData['Plan']['price'] + ($plateCount - $planData['Plan']['plate']) / 50 * $planData['Plan']['price_add'];
                    } elseif ($paymentType == 2) { // pay at year
                        $price = $planData['Plan']['price_year'] + ($plateCount - $planData['Plan']['plate']) / 50 * $planData['Plan']['price_add'] * 12;
                    }
                }
                $date = date('Y-m-d H:i:s');
                $insertData = array(
                    'account_user_id' => $accountID,
                    'price' => $price,
                    'regist_date' => $date
                );
                $this->Invoice->create();
                if ($this->Invoice->save($insertData)) {
                    $invoiceID = $this->Invoice->getLastInsertId();
                    $invoiceBDTag = $this->AccessLog->find('count', array(
                        'conditions' => array(
                            'team_id' => $pID
                        )
                    ));
                    $insertInvoiceBD = array(
                        'invoice_id' => $invoiceID,
                        'team_id' => $pID,
                        'tag' => $invoiceBDTag
                    );
                    $this->Invoice->InvoiceBreakdown->create();
                    $this->Invoice->InvoiceBreakdown->save($insertInvoiceBD);
                }
            }
        }
    }
    
    public function getAccountUser($id) {
        $this->Team->recursive = 0;
        $teamData = $this->Team->findById($id);
        $adminId = $teamData['Management']['parent_id'];
        $accountUserId = $this->AccountUser->find('first', array(
            'conditions' => array(
                'admin_id' => $adminId
            ),
            'fields' => 'id'
        ));
        if ($accountUserId)
            return $accountUserId['AccountUser']['id'];
    }
}
?>
