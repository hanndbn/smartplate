<?php

App::uses('AppHelper', 'View/Helper');
/**
 * Label helper class
 *  
 * @package       app.View.Helper
 * 
 */
class LabelHelper extends AppHelper 
{
    public $helpers = array('Html');

    /**
     * Render the HTML of Label's Hierachy
     */
    public function renderNestedLabels($labels, $count = array(), &$level = 0)
    {
        if ($labels)
        {
            $ol = ($level == 0) ? 'sortable' : '';

            $template = '<ol class="dd-list ' . $ol . '">';

            $level++;

            foreach ($labels as $label) 
            {                
                $status = $label['status'] ? 'circle-thin' : 'times';
                $contentCount = isset($count[$label['id']]['total']) ? $count[$label['id']]['total'] : 0;

                $list = '       
                    <li class="dd-item dd3-item" id="menuItem_' . $label['id'] . '" data-id="' . $label['id'] . '" data-status="' . $label['status'] . '" data-delete="' . Router::url(array('controller' => 'labels', 'action' => 'delete', $label['id']), true) . '">
                        <div>
                            <span class="dd-handle dd3-handle"></span>
                            <span class="dd3-content">                            
                                <span class="label-name lb-node" data-edit="' . Router::url(array('controller' => 'labels', 'action' => 'edit', $label['id']), true) . '" title="' . Utility_Str::escapehtml($label['label']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($label['label']), 35) . '</span>
                                <span class="list-inline pull-right">
                                    <span class="count lb-node" data-edit="' . Router::url(array('controller' => 'labels', 'action' => 'edit', $label['id']), true) . '">' . $contentCount . '</span>
                                    <span class="action lb-node" data-edit="' . Router::url(array('controller' => 'labels', 'action' => 'edit', $label['id']), true) . '"><input type="checkbox" class="label-item ignore-modal" autocomplete="off" value="' . $label['id'] . '"></span>
                                    <span class="status lb-node" data-edit="' . Router::url(array('controller' => 'labels', 'action' => 'edit', $label['id']), true) . '"><i class="fa fa-' . $status . '"></i></span>
                                </span>                               
                            </span>
                        </div>
                        ' . $this->renderNestedLabels($label['children'], $count, $level) . ' 
                    </li>';

                $template .= $list;         
            }

            $template .= '</ol>';

            return $template;
        }
    }

    /**
     * Render the HTML of Label's Hierachy for dropdown menu
     */
    public function renderDropdownLabels($labels, $class = '') 
    {
        if ($labels) 
        {
            $template = '<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">';

            foreach ($labels as $label) {
                if ($label['children'] == null) {
                    $list = '<li><a class="label_name ' . $class . '" href="#" data-id="'.$label['id'].'" title="'.Utility_Str::escapehtml($label['label']).'">' . Utility_Str::wordTrim(Utility_Str::escapehtml($label['label']), 8) . '</a></li>';
                } else {
                    $list = '    
                    <li class="dropdown-submenu"><a class="label_name  ' . $class . '" href="#" tabindex="-1" data-id="'.$label['id'].'" title="'.Utility_Str::escapehtml($label['label']).'">' . Utility_Str::wordTrim(Utility_Str::escapehtml($label['label']), 8) . '</a>' . $this->renderDropdownLabels($label['children'], $class) . ' 
                    </li>';
                }
                $template .= $list;
            }
               
            $template .= '</ul>';

            return $template;
        }
    }

    /**
     * Render the HTML of Label's Hierachy for dropdown in edit action
     */
    public function _renderEditDropdownLabels($labels, $ids, $class ) 
    {
        if ($labels) 
        {
            $template = '<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">';

            foreach ($labels as $label) {
                if ($label['children'] == null) {
                    $list = '<li><a class="label_edit' . $class . '" href="#" data-id="'.$label['id'].'" data-value="'.$ids.'" title="'.Utility_Str::escapehtml($label['label']).'">' . Utility_Str::wordTrim(Utility_Str::escapehtml($label['label']), 5) . '</a></li>';
                } else {
                    $list = '    
                    <li class="dropdown-submenu"><a class="label_edit' . $class . '" href="#" tabindex="-1" data-id="'.$label['id'].'" data-value="'.$ids.'" title="'.Utility_Str::escapehtml($label['label']).'">' . Utility_Str::wordTrim(Utility_Str::escapehtml($label['label']), 5) . '</a>' . $this->_renderEditDropdownLabels($label['children'], $ids, $class) . ' 
                    </li>';
                }
                $template .= $list;
            }

            $template .= '</ul>';
            return $template;
        }
    }

    /**
     * Render the HTML of Label's Hierachy for dropdown in edit action
     */
    public function renderEditDropdownLabels($labels, $ids, $class = '') 
    {
        if ($labels) 
        {
            $template = '<span class="caret" id="caret_'.$ids.'"></span>';
            $template .= $this->_renderEditDropdownLabels($labels, $ids, $class);
            $template .= '<div class="delete_label" data-id="0" data-value="'.$ids.'" title="delete"><img src="/img/delete.png" height="20px"/></div>';
            $input = '<input id="InputLabel_' .$ids. '" type="hidden" name="data[_label]['.$ids.']" value="'.$ids.'">';
            $template .= $input;
            return $template;
        }
    }
}
?>


<script type="text/javascript">
        // add geo
        $('.delete_label').click(function (e) {
            var value = $(this).attr('data-value');
            $('#Label_dropdowm_' + value).remove();
            $('#caret_' + value).remove();
            $('#InputLabel_' + value).val(-value);
            $(this).remove();
        });
        
</script>