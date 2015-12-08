<?php

App::uses('AppController', 'Controller');

class RedirectController extends AppController {
    private $banner_id;

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'redirect', 'action' => 'index'));
    }

    function get_banner_id($tag) {
        date_default_timezone_set('UTC');

        if (strpos($tag['tag'], "QT.10000.") !== false) {
            return 'QRT';
        }

        if ($tag['id'] == 41 || (101 <= $tag['id'] && $tag['id'] <= 110)) {
            return 'burger';
        } else if (111 <= $tag['id'] && $tag['id'] <= 120) {
            return 'drink';
        } else if (121 <= $tag['id'] && $tag['id'] <= 130) {
            return 'sashimi';
        } else if ($tag['team_id'] == 1001) {// DIYTool
            return 'carryfree';
        } else if ($tag['team_id'] == 1003) {// Daitou
            return 'spc';
        } else if ($tag['team_id'] == 1004) {// umechika
            return 'spc';
        } else if ($tag['team_id'] == 1006) {// aden
            return 'spc';
        } else if ($tag['team_id'] == 1017) {// ace
            return 'check';
        }
        return 'sp';
        //default smartplate
    }

    function index() {
        $error_flag = false;
        $log_url = "";
        $plate_id = '';
        try {

            $this->autoRender = false;

            $tag = $this->request->query('k');
            $this->loadModel('Tag');
            $this->loadModel('TagHistory');
            $this->loadModel('Link');
            $this->loadModel('Team');
            $this->loadModel('Bookmark');

            if (strlen($tag) == 8) {
                $type_char = substr($tag, 0, 1);
                $ext_index = substr($tag, 1, 7);
                $tag_data = $this->Tag->find('first', array('conditions' => array('ext_index' => $ext_index)));
                if ( !empty($tag_data)) {
                    $tag = $tag_data['Tag']['tag'];
                    $plate_id = substr($tag, 0, 2).substr($tag, 3,5).$type_char.substr($tag, 9,7);
                }
            } else {
                $tag = $this->Tag->TagURLtoTag($tag);
                $tag_data = $this->Tag->find('first', array('conditions' => array('tag' => $tag)));
            }
            if (empty($tag_data)) {
                throw new OutOfBoundsException($tag, 302);
            }
            $tag_data = $tag_data['Tag'];

            if (empty($tag_data['available'])) {
                throw new OutOfBoundsException($tag, 302);
            }

            $last_limit_data = $this->TagHistory->GetLastLimitDateByTagID($tag_data['id']);
            if (!empty($last_limit_data['limit_date'])) {

                $t = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone(date_default_timezone_get()));
                $t->setTimeZone(new DateTimeZone('UTC'));
                $now_date = $t->format('Y-m-d H:i:s');

                if ($last_limit_data['limit_date'] < $now_date) {
                    $tag_data['available'] = 0;
                    throw new OutOfBoundsException($tag, 302);
                }
            }

            $url = $this->Link->GetURLByTag($tag_data);

            if (empty($url)) {
                $bm = $this->Bookmark->find('first', array('conditions' => array('id' => $tag_data['bookmark_id'])));
                if (empty($bm)) {
                    throw new OutOfBoundsException($tag, 302);
                } else {
                    $bm = $bm['Bookmark'];
                    $url = $bm['url'];
                }
            } else {
                if (!empty($tag_data['bookmark_id'])) {
                    $bm['id'] = $tag_data['bookmark_id'];
                }
            }

            // url for access log
            $log_url = $url;

            if (strpos($tag, 'PW.') !== false) {
            } else {
                $splash = $this->Team->splashImage($tag_data['team_id']);
                if (empty($splash)) {
                    $this->banner_id = $this->get_banner_id($tag_data);

                    if ($this->banner_id) {
                        $url = sprintf('http://plate.id/ad/%s.php?url=%s', $this->banner_id, urlencode($url));
                    }
                } else {
                    $url = sprintf('http://plate.id/ad/splash.php?url=%s&splash=%s', urlencode($url), $splash);
                }
            }

        } catch (Exception $e) {
            $url = '';
            if (!empty($tag_data['team_id'])) {
                $team_data = $this->Team->find('first', array('condtion' => array('id' => $tag_data['team_id'])));
                if (!empty($team_data)) {
                    $team_data = $team_data['Team'];
                    if (!empty($team_data['default_url'])) {
                        $url = $team_data['default_url'];
                    }
                }
            } else {
                $tag_data['team_id'] = 0;
            }

            if (empty($tag_data['available']) && isset($tag_data['tag'])) {
                $url = sprintf('http://plate.id/ad/out_of_service.php?plate=%s', $tag_data['tag']);
            }

            if (empty($url)) {
                $url = 'http://spirals.co.jp/smartplate/ja/';
            }

            if ($this->banner_id) {
                $url = sprintf('http://plate.id/ad/%s.php?url=%s', $this->banner_id, urlencode($url));
            }
            $error_flag = true;
        }
        if (!empty($bm)) {
            $bookmark_id = $bm['id'];
        } else {
            $bookmark_id = 0;
        }

        if (empty($url)) {
            $url = 'http://spirals.co.jp/smartplate/ja/';
        }
        $result = array('url' => $url, 'log_url' => $log_url, 'team_id' => $tag_data['team_id'], 'bookmark_id' => $bookmark_id, 'plate_id' => $plate_id, 'error' => $error_flag);

        print json_encode($result);
    }

}
?>
