<?php

App::uses('AppModel', 'Model');
App::uses('Link', 'Model');
App::uses('CakeSession', 'Model/Datasource');
/**
 * Application model for bookmark table.
 *
 * @package       app.Model
 */
class Bookmark extends AppModel {

    /**
     * Link type
     */
    const TYPE_NORMAL = 0;
    const TYPE_OS = 1;
    const TYPE_TAILS = 2;
    const TYPE_RANDOM = 3;
    const TYPE_ROTATE = 4;
    const TYPE_MEDIA = 5;
    const TYPE_APPS = 6;
    const TYPE_PROGRAMMABLE = 7;
    const TYPE_LUCKY_DRAW = 8;
    const TYPE_MULTI_CONTENTS = 9;
    const TYPE_MY_PROFILE = 10;

    /**
     * icon name
     */
    const ICON_OS = "ic_bm_switch.png";
    const ICON_TAILS = "ic_bm_tiles.png";
    const ICON_RANDOM = "ic_bm_random.png";
    const ICON_ROTATE = "ic_bm_rotation.png";

    public $useTable = 'bookmark';

    public $validate = array('kind' => array('nonEmpty' => array('rule' => array('notEmpty'), 'message' => 'A Type is required', 'allowEmpty' => false)), 'team_id' => array('nonEmpty' => array('rule' => array('notEmpty'), 'message' => 'A team_id is required', 'allowEmpty' => false)), 'visible' => array('nonEmpty' => array('rule' => array('notEmpty'), 'message' => '', 'allowEmpty' => false)), 'name' => array('nonEmpty' => array('rule' => array('notEmpty'), 'message' => 'A Name is required', 'allowEmpty' => false)), 'url' => array('nonEmpty' => array(
    //'rule' => array('notEmpty'),
        'message' => 'URL is required', 'allowEmpty' => true, 'on' => 'create')),
    /*'image' => array(
     'nonEmpty' => array(
     //'rule' => array('notEmpty'),
     'message' => 'Images is required',
     'allowEmpty' => true,
     'on' => 'create'
     ),
     ),*/
    );

    static function thumbnailURL($image_name) {
        $result = '';
        if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $image_name)) {
            $result = $image_name;
        } else {
            $image_names = explode('/', $image_name);
            $result = Router::fullbaseUrl() . DS . 'upload' . DS . 'bookmark' . DS . $image_names[0] . DS . "th" . $image_names[1];
        }
        return $result;
    }

    static function imageURL($image_name) {
        $result = '';
        if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $image_name)) {
            $result = $image_name;
        } else {
            $result = Router::fullbaseUrl() . DS . 'upload' . DS . 'bookmark' . DS . $image_name;
        }
        return $result;
    }

    /**
     * Parse data from CSV file
     *
     * @param String $filename CSV file name
     */
    function import($filename) {
        // to avoid having to tweak the contents of
        // $data you should use your db field name as the heading name
        // eg: Post.id, Post.title, Post.description
        // set the filename to read CSV from
        $filename = WWW_ROOT . 'upload' . DS . 'csv' . DS . $filename;
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // open the file
        $handle = fopen($filename, "r");

        // read the 1st row as headings
        $header = fgetcsv($handle);

        // create a message container
        $return = array('messages' => array());
        $datas = array();
        $i = 0;
        // read each data row in the file
        while (($row = fgetcsv($handle)) !== FALSE) {
            $i++;
            if (($row[0] == "") || ($row[2] == "")) {
                $return['messages'][] = __(sprintf('Bookmark for Row %d failed to validate.', $i), true);
                continue;
            }
            foreach ($row as $key => $value) {
                if ($header[$key] != 'url') {
                    $datas[$i]['Bookmark']['Bookmark'][$header[$key]] = $row[$key];
                } else {
                    $datas[$i]['Link']['Link']['url'] = $row[$key];
                }
            }
        }
        foreach ($datas as &$data) {
            $data['Bookmark']['Bookmark']['team_id'] = CakeSession::read("Auth.User.team_id");
            $data['Bookmark']['Bookmark']['cdate'] = date('Y-m-d H:i:s');
            $this->create();
            if ($this->save($data['Bookmark'])) {
                $bookmarkId = $this->getLastInsertId();
                $data['Link']['Link']['bookmark_id'] = $bookmarkId;
                $data['Link']['Link']['type'] = 0;
                $data['Link']['Link']['sub_type'] = 0;
                $data['Link']['Link']['user_id'] = CakeSession::read("Auth.User.id");
                $data['Link']['Link']['cdate'] = date('Y-m-d H:i:s');
                $link = new Link();
                $link->create();
                $link->save($data['Link']);
            }
        }
        // close the file
        fclose($handle);

        // return the messages
        return $return;
    }

    public function SelectByTeamWithKeyword($team_id, $keyword, $arg = null, &$total = null) {
        $team_id = intval($team_id);

        $options = array('fields' => array('Bookmark.*', "DATE_FORMAT( Bookmark.cdate - INTERVAL 9 HOUR,  '%a, %d %b %Y %H:%I:%S+00:00:00' ) as cdate"), 'joins' => array( array('type' => 'LEFT', 'table' => 'links', 'alias' => 'Link', 'conditions' => array('Link.bookmark_id = Bookmark.id'))), 'group' => array('Bookmark.id'), );
        $keyword = "%$keyword%";
        $options['conditions'] = array('Bookmark.team_id' => $team_id);
        $options['conditions']['OR'] = array('Bookmark.name LIKE' => $keyword, 'Bookmark.code LIKE' => $keyword, 'Bookmark.url LIKE' => $keyword, 'Link.url LIKE' => $keyword);

        $total = $this->find('count', $options);

        if (!empty($arg['order']))
            $options['order'] = array($arg['order'] => $arg['direction']);
        if (!empty($arg['limit']))
            $options['limit'] = $arg['limit'];
        if (!empty($arg['offset']))
            $options['offset'] = $arg['offset'];

        $res = $this->find('all', $options);

        $datas = array();
        foreach ($res as $key => $value) {
            if (!empty($value['Bookmark']['image'])) {
                $value['Bookmark']['image'] = self::thumbnailURL($value['Bookmark']['image']);
            }
            $datas[] = $value['Bookmark'];
        }

        return $datas;
    }

    public function SelectByTeamWithLabel($team_id, $label_id, $arg = null, &$total = null) {
        $team_id = $team_id;

        $options = array(
        //'fields' => array( 'Bookmark.*',"DATE_FORMAT( Bookmark.cdate - INTERVAL 9 HOUR,  '%a, %d %b %Y %H:%I:%S+00:00:00' ) as cdate" ),
            'fields' => array('Bookmark.*'), 'joins' => array( array('type' => 'LEFT', 'table' => 'label_datas', 'alias' => 'LabelData', 'conditions' => array('LabelData.target_id = Bookmark.id')), array('type' => 'LEFT', 'table' => 'label', 'alias' => 'Label', 'conditions' => array('Label.id = LabelData.label_id', "Label.type" => "BookmarkModel"))), );

        if ($label_id) {
            $options['conditions'] = array('Bookmark.team_id' => $team_id, 'LabelData.label_id' => $label_id);
        } else {
            $options['conditions'] = array('Bookmark.team_id' => $team_id, "LabelData.target_id IS NULL");
        }

        $total = $this->find('count', $options);

        if (!empty($arg['order']))
            $options['order'] = array($arg['order'] => $arg['direction']);
        if (!empty($arg['limit']))
            $options['limit'] = $arg['limit'];
        if (!empty($arg['offset']))
            $options['offset'] = $arg['offset'];

        $res = $this->find('all', $options);

        $datas = array();
        foreach ($res as $key => $value) {
            $value['Bookmark']['cdate'] = $this->convertDateTokyo2UTC($value['Bookmark']['cdate']);
            if (!empty($value['Bookmark']['image'])) {
                $value['Bookmark']['image'] = self::thumbnailURL($value['Bookmark']['image']);
            }
            $datas[] = $value['Bookmark'];
        }

        return $datas;
    }

    public function SelectByTeams($team_id, $arg = null, &$total = null) {

        $team_id = intval($team_id);

        $options = array('fields' => array('Bookmark.*', "DATE_FORMAT( Bookmark.cdate - INTERVAL 9 HOUR,  '%a, %d %b %Y %H:%I:%S+00:00:00' ) as cdate"), 'conditions' => array('Bookmark.team_id' => $team_id));

        $total = $this->find('count', $options);

        if (!empty($arg['order']))
            $options['order'] = array($arg['order'] => $arg['direction']);
        if (!empty($arg['limit']))
            $options['limit'] = $arg['limit'];
        if (!empty($arg['offset']))
            $options['offset'] = $arg['offset'];

        $res = $this->find('all', $options);

        $datas = array();
        foreach ($res as $key => $value) {
            if (!empty($value['Bookmark']['image'])) {
                $value['Bookmark']['image'] = self::thumbnailURL($value['Bookmark']['image']);
            }
            $datas[] = $value['Bookmark'];
        }

        return $datas;
    }

    public function InsertBookmark($team_id, $user_id, $name, $url) {
        $data = array();
        $this->create();
        $bookmarkId = 0;
        if ($this->save(array('team_id' => $team_id, 'name' => $name))) {
            $bookmarkId = $this->getLastInsertId();
            $data['Link']['Link']['bookmark_id'] = $bookmarkId;
            $data['Link']['Link']['type'] = 0;
            $data['Link']['Link']['sub_type'] = 0;
            $data['Link']['Link']['user_id'] = $user_id;
            $data['Link']['Link']['url'] = $url;
            $data['Link']['Link']['cdate'] = date('Y-m-d H:i:s');
            $link = new Link();
            $link->create();
            $link->save($data['Link']);
        }

        return $bookmarkId;
    }

    public function InsertBookmarkWithLink($team_id, $user_id, $name, $urls, $type, $ext_data = '') {
        $data = array();
        $this->create();
        $bookmarkId = 0;
        if (!empty($ext_data['image'])) {
            $image = $ext_data['image'];
        }
        if ($this->save(array('team_id' => $team_id, 'name' => $name, 'image' => $image))) {
            $bookmarkId = $this->getLastInsertId();
            $data['Link']['Link']['bookmark_id'] = $bookmarkId;
            $data['Link']['Link']['type'] = 0;
            $data['Link']['Link']['sub_type'] = 0;
            $data['Link']['Link']['user_id'] = $user_id;
            $data['Link']['Link']['url'] = "";
            $data['Link']['Link']['cdate'] = date('Y-m-d H:i:s');
            $link = new Link();

            if ($type != 9) {
                foreach ($urls as $sub_type => $url) {
                    $link->create();

                    $links_datas = array('tag_id' => 0, 'url' => $url, 'bookmark_id' => $bookmarkId, 'type' => $type, 'sub_type' => $sub_type, 'user_id' => $user_id, 'udate' => date("Y-m-d H:i:s"), 'cdate' => date("Y-m-d H:i:s"));

                    if ($type == 2) {
                        $links_datas['icon'] = $ext_data['icons'][$sub_type];
                        $links_datas['link_text'] = $ext_data['link_texts'][$sub_type];
                    }
                    $link->save($links_datas);
                }
            }
        }

        return $bookmarkId;
    }

    public function UpdateBookmark($bookmark_id, $team_id, $user_id, $name, $urls, $type, $ext_data = '') {
        $bookmark_data = array('id' => $bookmark_id, 'team_id' => $team_id);
        if (!empty($name)) {
            $bookmark_data['name'] = $name;
        }
        if (!empty($ext_data['image'])) {
            $bookmark_data['image'] = $ext_data['image'];
        }
        if ($this->save($bookmark_data)) {
            if (!empty($urls) && $type != null) {
                $link = new Link();
                $link->DeleteByBookmarkID($bookmark_id);

                if ($type != 9) {

                    foreach ($urls as $sub_type => $url) {

                        $links_datas = array('tag_id' => 0, 'url' => $url, 'bookmark_id' => $bookmark_id, 'type' => $type, 'sub_type' => $sub_type, 'user_id' => $user_id, 'udate' => date("Y-m-d H:i:s"), 'cdate' => date("Y-m-d H:i:s"));

                        if ($type == 2) {
                            $links_datas['icon'] = $ext_data['icons'][$sub_type];
                            $links_datas['link_text'] = $ext_data['link_texts'][$sub_type];
                        }
                        $link->save($links_datas);
                    }
                }
            }
        }
    }

}
?>
