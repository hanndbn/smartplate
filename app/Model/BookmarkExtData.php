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

    public $primaryKey = 'bookmark_id';
    public $useTable = 'bookmark_ext_data';
    public $belongsTo = array(
        'Bookmark' => array('className' => 'Bookmark',
            'foreignKey' => 'bookmark_id'));
}

