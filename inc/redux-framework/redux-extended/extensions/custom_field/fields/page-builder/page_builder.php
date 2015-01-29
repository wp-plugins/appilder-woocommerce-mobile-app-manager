<?php

/**
 * Options page_builder Field for Redux Options
 *
 * @author                      Yannis - Pastis Glaros <mrpc@pramnoshosting.gr>
 * @url                         http://www.pramhost.com
 * @license                     [http://www.gnu.org/copyleft/gpl.html GPLv3
 *                              This is actually based on:   [SMOF - Slightly Modded Options Framework](http://aquagraphite.com/2011/09/slightly-modded-options-framework/)
 *                              Original Credits:
 *                              Author:                      Syamil MJ
 *                              Author URI:                  http://aquagraphite.com
 *                              License:                     GPLv3 - http://www.gnu.org/copyleft/gpl.html
 *                              Credits:                     Thematic Options Panel - http://wptheming.com/2010/11/thematic-options-panel-v2/
 *                              KIA Thematic Options Panel:   https://github.com/helgatheviking/thematic-options-KIA
 *                              Woo Themes:                   http://woothemes.com/
 *                              Option Tree:                  http://wordpress.org/extend/plugins/option-tree/
 *                              Twitter:                     http://twitter.com/syamilmj
 *                              Website:                     http://aquagraphite.com
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ReduxFramework_page_builder')) {
    class ReduxFramework_page_builder
    {

        /**
         * Field Constructor.
         * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
         *
         * @since Redux_Options 1.0.0
         */
        function __construct($field = array(), $value = '', $parent)
        {
            $this->parent = $parent;
            $this->field = $field;
            $this->value = $value;
            $widgets = widget_handler::get_widgets();
            $wid_to_add = array();
            if(!isset($this->value['enabled']) || empty($this->value['enabled'])){
                $this->value['enabled']=array();
            }
            $this->field['options']['disabled']=array();
            $this->value['disabled']=array();
            foreach($widgets as $key=> $widget){
                $wid_to_add[$key] = array();
                if(isset($widget['items']) && !empty($widget['items'])){
                    foreach($widget['items'] as $id=> $item){
                        if(!empty($item['title']))
                        $wid_to_add[$key][$key."_".$id] = $item['title']." (#{$key}_{$id})";
                    }
                }elseif(isset($widget['slides']) && !empty($widget['slides'])){
                    foreach($widget['slides'] as $id=>$item){
                        if(!empty($item['title']))
                            $wid_to_add[$key][$key."_".$id] = $item['title']." (#{$key}_{$id})";
                    }
                }
            }
            $this->field['options']['disabled']=$wid_to_add;
            $this->value['disabled']=$wid_to_add;
        }
        function AddPageThinkBox(){
                    add_thickbox();
                    ?>
                    <div id="add-inPage-box" style="display:none;">
                        <p>
                            Page title  <input type="text" class="regular-text " value="" name="page_title" id="page_title-text" />
                            <button class="button button-primary" id="page_title-add-button">Add page</button>
                        </p>
                    </div>
                <?php
        }
        /**
         * Field Render Function.
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since 1.0.0
         */
        function render()
        {

            widget_handler::single_display("addpage_thinkbox",array($this,"AddPageThinkBox"));
            if (!is_array($this->value) && isset($this->field['options'])) {
                $this->value = $this->field['options'];
            }

            if (!isset($this->field['args'])) {
                $this->field['args'] = array();
            }

            if (isset($this->field['data']) && !empty($this->field['data']) && is_array($this->field['data'])) {
                foreach ($this->field['data'] as $key => $data) {
                    if (!isset($this->field['args'][$key])) {
                        $this->field['args'][$key] = array();
                    }
                    $this->field['options'][$key] = $this->parent->get_wordpress_data($data, $this->field['args'][$key]);
                }
            }

            // Make sure to get list of all the default blocks first
            $all_blocks = !empty($this->field['options']) ? $this->field['options'] : array();
            $temp = array(); // holds default blocks
            $temp2 = array(); // holds saved blocks

            foreach ($all_blocks as $blocks) {
                $temp = array_merge($temp, $blocks);
            }

            $sortlists = $this->value;

            if (is_array($sortlists)) {
                foreach ($sortlists as $sortlist) {
                    $temp2 = array_merge($temp2, $sortlist);
                }

                // now let's compare if we have anything missing
                foreach ($temp as $k => $v) {
                    if (!array_key_exists($k, $temp2)) {
                        $sortlists['disabled'][$k] = $v;
                    }
                }
   /*

                Commented for flexible
                // now check if saved blocks has blocks not registered under default blocks
                foreach ($sortlists as $key => $sortlist) {
                    foreach ($sortlist as $k => $v) {
                        if (!array_key_exists($k, $temp)) {
                            unset($sortlist[$k]);
                        }
                    }
                    $sortlists[$key] = $sortlist;
                }

                print_r($sortlists);

                // assuming all sync'ed, now get the correct naming for each block
                foreach ($sortlists as $key => $sortlist) {
                    foreach ($sortlist as $k => $v) {
                        $sortlist[$k] = $temp[$k];
                    }
                    $sortlists[$key] = $sortlist;
                }

*/
                if ($sortlists) {
                    echo '<fieldset id="' . $this->field['id'] . '" class="redux-page_builder-container redux-page_builder">';

                    foreach ($sortlists as $group => $sortlist) {
                        $filled = "";

                        if (isset($this->field['limits'][$group]) && count($sortlist) >= $this->field['limits'][$group]) {
                            $filled = " filled";
                        }
                        if($group == "disabled" || $group=="widgets"){
                            $class_sortSource = "sortSource";
                        }else
                            $class_sortSource ="";
                        if($group == "disabled"){
                            $group_title= "Widgets";
                        }elseif($group == "enabled"){
                            $group_title="Layout";
                        }else{
                            $group_title=$group;
                        }
                        echo '<ul id="' . $this->field['id'] . '_' . $group . '" class="sortlist_' . $this->field['id'] . $filled . ' '.$class_sortSource.'" data-id="' . $this->field['id'] . '" data-group-id="' . $group . '">';
                        echo '<h3>' . $group_title . '</h3>';
                        if (!isset($sortlist['placebo'])) {
                            array_unshift($sortlist, array("placebo" => "placebo"));
                        }
                        foreach ($sortlist as $key => $list) {
                            if (is_array($list) && $key!==0) {
                                echo "<div class='page_builder_acc'>
                                    <h3>$key</h3><div>";
                                    foreach ($list as $key1 => $list1) {
                                        echo '<input class="page_builder-placebo" type="hidden" name="' . $this->field['name'] . '[' . $group . '][placebo]' . $this->field['name_suffix'] . '" value="placebo">';
                                            echo '<li id="'.$key1.'" class="'.$key1.'" >';
                                                echo '<input class="position ' . $this->field['class'] . '" type="hidden" name="' . $this->field['name'] . '[' . $group . '][' . $key1 . ']' . $this->field['name_suffix'] . '" value="' . $list1 . '">';
                                                echo $list1;
                                             echo '</li>';
                                    }
                                echo "</div></div>";
                            } else {
                                echo '<input class="page_builder-placebo" type="hidden" name="' . $this->field['name'] . '[' . $group . '][placebo]' . $this->field['name_suffix'] . '" value="placebo">';
                                if ($key != "placebo" ) {
                                    echo '<li id="' . $key . '" class="sortee ' . $key . '">';
                                    echo '<input class="position ' . $this->field['class'] . '" type="hidden" name="' . $this->field['name'] . '[' . $group . '][' . $key . ']' . $this->field['name_suffix'] . '" value="' . $list . '">';
                                    echo $list;
                                    echo '</li>';
                                }
                            }
                        }
                        echo '</ul>';
                    }
                    echo '</fieldset>';
                }
            }
        }

        function enqueue()
        {

            wp_enqueue_style(
                'redux-field-page-builder-css',
                plugins_url('page-builder.css', __FILE__),
                time(),
                true
            );

            wp_enqueue_script(
                'redux-field-page-builder-js',
                plugins_url('page-builder.js', __FILE__),
                array('jquery', 'redux-js'),
                time(),
                true
            );
        }

        /**
         * Functions to pass data from the PHP to the JS at render time.
         *
         * @param $field
         * @param string $value
         * @return array Params to be saved as a javascript object accessable to the UI.
         * @since  Redux_Framework 3.1.5
         */
        function localize($field, $value = "")
        {

            $params = array();

            if (isset($field['limits']) && !empty($field['limits'])) {
                $params['limits'] = $field['limits'];
            }

            if (empty($value)) {
                $value = $this->value;
            }
            $params['val'] = $value;

            return $params;
        }
    }
}