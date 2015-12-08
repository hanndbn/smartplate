<?php

App::uses('FormHelper', 'View/Helper');

/**
 * Extend the FormHelper class
 *  
 * @package       app.View.Helper
 * 
 */
class ExtendedFormHelper extends FormHelper {

    /**
     * Disable HTML5 auto complete for input
     * 
     * @param string $fieldName This should be "Modelname.fieldname"
     * @param array $options Each type of input takes different options.
     * @return string Completed form widget.
     */
    public function input($fieldName, $options = array()) {
        $options['autocomplete'] = 'off';

        $input = parent::input($fieldName, $options);

        return $input;
    }

    /**
     * @param string $fieldName Name of a field, like this "Modelname.fieldname"
     * @param array $options Array of HTML attributes.
     * @return string An HTML text input element.
     */
    public function checkbox($fieldName, $options = array()) {
        $options['autocomplete'] = 'off';

        $checkbox = parent::checkbox($fieldName, $options);

        return $checkbox;
    }

    /**
     * @param string $fieldName Name of a field, like this "Modelname.fieldname"
     * @param array $options Radio button options array.
     * @param array $attributes Array of HTML attributes, and special attributes above.
     * @return string Completed radio widget set.
     */
    public function radio($fieldName, $options = array(), $attributes = array()) {
        $options['autocomplete'] = 'off';

        $radio = parent::radio($fieldName, $options, $attributes);

        return $radio;
    }

    /**
     * @param string $fieldName Name of a field, in the form "Modelname.fieldname"
     * @param array $options Array of HTML attributes, and special options above.
     * @return string A generated HTML text input element
     */
    public function textarea($fieldName, $options = array()) {
        $options['autocomplete'] = 'off';

        $textarea = parent::textarea($fieldName, $options);

        return $textarea;
    }

}

