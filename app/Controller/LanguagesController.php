<?php

App::uses('AppController', 'Controller');

/**
 * Convert a comma-separated value CSV file to Gettext PO format.
 * 
 * @package       app.Controller
 */
class LanguagesController extends AppController {

    public $uses = null;

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('system_index');
    }
    
    public function index() {
        
    }
    
    /**
     * Convert a comma-separated value CSV file to Gettext PO format.
     */
    public function system_index() {
        if ($this->request->is('post') || $this->request->is('put')) {
            $lang = $this->request->data['Language']['langBtn'];
            $path = APP . 'Locale' . DS . $lang . DS . 'LC_MESSAGES';
            $csv_file = $path . DS . "input.csv";

            $locale = $lang;
            $id = 'iSpot';
            $source = basename($csv_file);

            $po_file = $path . DS . 'default.po';

            $col_msgid = 0;  # 0-based.
            $col_msgstr = 1;
            $col_notes = 2;


            $duplicates_check = array();
            $output = $this->get_header($locale, $source, $id);
            $row = 0;
            if (($handle = fopen($csv_file, "r")) !== FALSE) {

                while (($data = fgets($handle, 1000)) !== FALSE) {
                  $data = rtrim($data);
                  $data = explode(',', $data);
                    $row++;

                    if (!isset($data[$col_msgid]) || '' == $data[$col_msgid]) {
                        echo "Skipping empty msgid, row: $row" . PHP_EOL;
                        continue;
                    }

                    $msgid = addslashes($data[$col_msgid]);
                    if (in_array($msgid, $duplicates_check)) {
                        echo "Skipping duplicate: $msgid" . PHP_EOL;
                        continue;
                    }
                    $duplicates_check[] = $msgid;

                    if (isset($data[$col_notes])) {
                        $output .= PHP_EOL . "#. Context: $row, " . $data[$col_notes] . PHP_EOL;
                    }

                    $output .= 'msgid "' . $msgid . '"' . PHP_EOL;
                    if (isset($data[$col_msgstr])) {
                        $output .= 'msgstr "' . addslashes($data[$col_msgstr]) . '"' . PHP_EOL;
                    } else {
                        $output .= 'msgstr ""' . PHP_EOL;
                    }
                }
                fclose($handle);
            } else {
                $this->Session->setFlash(__('CVSファイルオープン中にエラーが発生しました。 ').$csv_file, 'alert-box', array('class' => 'alert-danger'));
                $this->redirect(array('action' => 'index'));
            }
            // Create PO file
            $fh = fopen($po_file, 'w');
            if (fwrite($fh, $output)) {
                $this->Session->setFlash(__('ファイルのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('プレートをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                $this->redirect(array('action' => 'index'));
            }
            fclose($fh);
//            $bytes = file_put_contents($po_file, $output);
        }
        $this->render('index');
    }
    
    /**
     * Write header for PO file
     */
    function get_header($locale = NULL, $source = NULL, $id = NULL, $charset = 'UTF-8') {
        return <<<EOH
# $id language/translation.
#
# Source:  $source
#
# Copyright (c) 2013 The Open University.
# This file is distributed under the same license as the PACKAGE package.
# IET-OU <EMAIL@ADDRESS>, YEAR.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: $id\\n"
"Report-Msgid-Bugs-To: iet-webmaster+@+open.ac.uk\\n"
"POT-Creation-Date: 2013-10-02 14:00+0100\\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"
"Language-Team: LANGUAGE <LL@li.org>\\n"
"Language: $locale\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/html; charset=$charset\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\n"


EOH;
    }

}
?>
