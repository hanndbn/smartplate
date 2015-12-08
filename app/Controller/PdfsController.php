<?php
App::import('Vendor', 'mpdf' . DS . 'mpdf');

class PdfsController extends AppController {

    public $uses = null;

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Invoice');
    }

    public function createPdf($id) {
        // get invoice data
        $this->Invoice->recursive = 0;
        $invoice = $this->Invoice->findById($id);      
        $invoice['Invoice']['tax'] = (8 / 100) * $invoice['Invoice']['price'];
        $invoice['Invoice']['totalprice'] = $invoice['Invoice']['price'] + $invoice['Invoice']['tax'];
        $firstthree = substr($invoice['AccountUser']['zip_code'], 0, 3);
        $lastfour = substr($invoice['AccountUser']['zip_code'], 3);
        $invoice['AccountUser']['zip_code'] = $firstthree.'-'.$lastfour;
        $invoiceDatas = $this->Invoice->InvoiceBreakdown->find('all', array(
            'conditions' => array(
                'invoice_id' => $id
            ),
            'fields' => array('invoice_id', 'team_id', 'tag', 'contents')
        ));
        $invoice['Invoice']['price'] = number_format($invoice['Invoice']['price']);
        $invoice['Invoice']['tax'] = number_format($invoice['Invoice']['tax']);
        $invoice['Invoice']['totalprice'] = number_format($invoice['Invoice']['totalprice']);
        if ($invoiceDatas) {
            $invoice['Invoice']['team_id'] = $invoice['Invoice']['tag'] = $invoice['Invoice']['contents'] = null;
            foreach ($invoiceDatas as $data) {
                $invoice['Invoice']['team_id'][] = $data['InvoiceBreakdown']['team_id'];
                $invoice['Invoice']['tag'][] = $data['InvoiceBreakdown']['tag'];
                $invoice['Invoice']['contents'][] = $data['InvoiceBreakdown']['contents'];                
            }
        }
        // create new PDF document
        $output_file = 'S' . str_pad($id, 4, '0', STR_PAD_LEFT) . '.pdf';


// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
// create some HTML content
        $html = '
    <body style="font-family: HanaMinA; font-weigth: light; font-size: 14px;">
        <div id="header" style="overflow: hidden;">
            <div style="float: left; width: 50%; text-align: left">
                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>
                        <tr>
                            <td style="text-align: left; width: 15%;">' . __('所在地') . '</td>
                            <td style="padding: 5px; border-left: 1px solid #ccc; border-top: 1px solid #ccc; border-right: 1px solid #ccc; border-bottom: 1px dotted #ccc;">〒 <span style="color: rgb(255,38,0);">' . $invoice['AccountUser']['zip_code'] . ' </span>' . __('ご請求書在中') . '</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; width: 15%;"></td>
                            <td style="padding: 5px; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-bottom: 1px dotted #ccc; color: rgb(255,38,0);">
                                ' . $invoice['AccountUser']['region'] . ' ' . $invoice['AccountUser']['city'] . '
                            </td>
                        </tr>                        
                        <tr>
                            <td style="text-align: left; width: 15%;"></td>
                            <td style="padding: 5px; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-bottom: 1px dotted #ccc; color: rgb(255,38,0);">
                                ' . $invoice['AccountUser']['address'] . '
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left; width: 15%;">' . __('取引先') . '</td>
                            <td style="padding: 5px; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-bottom: 1px dotted #ccc; color: rgb(255,38,0);">' . $invoice['AccountUser']['company'] . '</td>
                        </tr>  
                        <tr>
                            <td style="text-align: left; width: 15%;"></td>
                            <td style="padding: 5px; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-bottom: 1px dotted #ccc; color: rgb(255,38,0);">' . $invoice['AccountUser']['department'] . '</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; width: 15%;">' . __('担当者') . '</td>
                            <td style="padding: 5px; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-bottom: 1px solid #ccc;"><span style="color: rgb(255,38,0);">' . $invoice['AccountUser']['family_name'] . ' ' . $invoice['AccountUser']['given_name'] . '</span> ' . __('様') . '</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="float: right; width: 50%; text-align: right">
                <div style="float: right; width: 80%; border-bottom: 1px solid #ccc; line-height: 190%; margin-right: 5px;">
                    ' . __('発行日') . ':<span style="color: rgb(255,38,0);">' .date('Y')._('年').date('m').__('月').date('d').__('日'). '</span>
                </div>
                <div style="float: right; width: 80%; border-bottom: 1px solid #ccc; line-height: 190%; margin-right: 5px;">
                    ' . __('請求コード') . ':S<span style="color: rgb(255,38,0);">' . str_pad($id, 4, '0', STR_PAD_LEFT) . '</span>
                </div>
                <img src="'.IMAGES_URL.'marker.png" width="300" />
            </div>
        </div>
        <div id="content" style="margin: 30px 0 0 0;">
            <div id="title" style="margin-bottom: 30px; height: 40px; background-color: #111; color: #fff; font-size: 35px; text-align: center;">' . __('御請求書') . '</div>
            <div style="width: 90%; margin: 0 auto;">
                <div style="margin-bottom: 10px;">
                    <span style="font-weight: bold;">' . __('平素は格別のお引き立てを賜り、厚く御礼申し上げます。') . '</span><br>
                    <span style="font-weight: bold;">' . __('下記の通りご請求申し上げます。') . '</span><br>
                    <span style="font-weight: bold;">' . __('今後ともよろしくお願いいたします。') . '</span>
                </div>
                <div style="margin-bottom: 20px; overflow: hidden;">
                    <table>
                        <tbody>
                            <tr>
                                <td>' . __('請求額合計') . ':</td>
                                <td style="border-bottom: 1px dotted #ccc; font-weight: bold; font-size: 30px; color: rgb(255,38,0);">'.$invoice['Invoice']['totalprice'].'</td>
                                <td>' . __(' 円') . '</td>
                                <td style="width: 200px;"></td>
                                <td>' . __('お支払い期日') . ':</td>
                                <td style="border-bottom: 1px dotted #ccc; font-weight: bold; font-size: 20px; color: rgb(255,38,0);">' . date('Y/m/t', strtotime('+ 1 month')) . '</td>
                            </tr>
                        </tbody>
                    </table>                    
                </div>
                <div style="margin-left: 255px; margin-bottom: 10px; overflow: hidden;">
                    <table style="border: 1px solid #ccc; border-collapse: collapse;">
                        <tbody>
                            <tr>
                                <td style="padding: 5px 15px; border-right: 1px solid #ccc;">' . __('消費税') . '</td>
                                <td style="padding: 5px 15px; border-right: 1px solid #ccc; color: rgb(255,38,0); font-weight: bold;">￥'.$invoice['Invoice']['tax'].'</td>
                                <td style="padding: 5px 15px; border-right: 1px solid #ccc;">' . __('合計取引金額') . '</td>
                                <td style="padding: 5px 15px; color: rgb(255,38,0); font-weight: bold;">￥' . $invoice['Invoice']['price'] . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="overflow: hidden; margin: 0px auto;">
                    <div style="float: left; width: 10%; margin-left: 15px; font-size: 50px;">(</div>
                    <div style="float: left; width: 10%; margin-left: 595px; font-size: 50px;">)</div>
                </div>
            </div>
        </div>
        <div id="table-content">
            <table style="width: 90%; margin: 0 auto; text-align: center; border-collapse: collapse; border: 1px solid #ccc;">
                <thead>
                    <tr style="background-color: #111;">
                        <th style="color: #fff; border-right: 1px solid #ccc;">' . __('分類') . '</th>
                        <th style="color: #fff; border-right: 1px solid #ccc;">' . __('見積コード') . '</th>
                        <th style="color: #fff; border-right: 1px solid #ccc;">' . __('納品日') . '</th>
                        <th style="color: #fff; border-right: 1px solid #ccc;">' . __('件名') . '</th>
                        <th style="color: #fff; border-right: 1px solid #ccc;">' . __('金額') . '</th>
                        <th style="color: #fff;">' . __('備考') . '</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;">4</td>                       
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; color: rgb(255,38,0); border: 1px solid #ccc;">' . date('Y/m/t') . '</td>
                        <td style="height: 30px; border: 1px solid #ccc;">SmartPlate</td>
                        <td style="height: 30px; color: rgb(255,38,0); border: 1px solid #ccc;">' . $invoice['Invoice']['price'] . '</td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                        <td style="height: 30px; border: 1px solid #ccc;"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="footer" style="width: 90%; margin: 0 auto; overflow: hidden; border: 1px solid #ccc; margin-top: 10px;">
            <div style="padding: 10px;">
                <div style="float: left; width: 49%;">' . __('恐れ入りますが右記口座へお振込ください。') . '</div>
                <div style="float: left; width: 50%; margin-left: 5px; font-weight: bold;">' . __('みずほ銀行　麻布支店　普通　1086539 株式会社アクアビットスパイラルズ') . '</div><br>
                <div style="text-align: center;">' . __('誠に勝手ながら振込手数料は御社にてご負担下さいますようお願い申し上げます。') . '</div>
            </div>    
        </div>        
    </body>
   
';
        $footer = array(
            'L' => array(
                'content' => '',
                'font-size' => 10,
                'font-style' => 'B',
                'font-family' => 'serif',
                'color' => '#000000'
            ),
            'C' => array(
                'content' => '2014 Aquabit Spirals Inc.',
                'font-size' => 10,
                'font-style' => 'B',
                'font-family' => 'time',
                'color' => '#000000'
            ),
            'R' => array(
                'content' => '',
                'font-size' => 10,
                'font-style' => 'B',
                'font-family' => 'serif',
                'color' => '#000000'
            ),
            'line' => 1,
        );
        $pdf = new mPDF('', 'A4', '', '', 10, 10, 20, 20, 10, 10);
        
        $pdf->defaultfooterfontsize=12;
        $pdf->defaultfooterfontstyle = '';
        @$pdf->SetFooter('|2014 Aquabit Spirals Inc.|{PAGENO}');
        //@$pdf->DefHTMLFooterByName('Chapter2Footer','Chapter 2 Footer');
        @$pdf->WriteHTML($html);
        @$pdf->Output($output_file, 'D');
        exit;
    }
    
    public function system_createPdf($id) {
        $this->createPdf($id);
    }

}

?>
