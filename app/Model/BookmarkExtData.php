<?php
App::uses('AppModel', 'Model');

/**
 * Application model for Bookmark ext data table.
 *
 * @package       app.Model
 */
class BookmarkExtData extends AppModel
{

    const EXT_TITLE    = 'title';
    const EXT_HEADER   = 'header';
    const EXT_FOOTER   = 'footer';
    const EXT_TITLE_HEADER_IMAGE   = 'title_header_image';

    public $useTable = 'bookmark_ext_data';
    public $belongsTo = array(
        'Bookmark' => array('className' => 'Bookmark',
            'foreignKey' => 'bookmark_id'));
}

