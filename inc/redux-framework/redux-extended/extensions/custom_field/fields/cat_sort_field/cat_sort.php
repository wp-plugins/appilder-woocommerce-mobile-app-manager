<?php
class ReduxFramework_cat_sort
{

    /**
     * Field Constructor.
     *
     * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
     * @since Redux_Options 2.0.1
     */
    function __construct($field = array(), $value = '', $parent)
    {

        // parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->field = $field;
		$this->value = $value;
    }

    /**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for the field in the settings
     *
     * @since Redux_Options 2.0.1
     */
    function render()
    {
        $this->enqueue();
        $class = (isset($this->field['class'])) ? $this->field['class'] : '';
        $options = $this->field['options'];
        // This is to weed out missing options that might be in the default
        // Why?  Who knows.  Call it a dummy check.

        $noSort = false;
		
		if(empty($this->value)) $this->value=array();
		foreach ($options as $k => $v) {
		$k = $v['id'];
		if (isset($this->value[$k]) && !is_array($this->value[$k])) {
            parse_str($this->value[$k],$value_options);
            $this->value[$k] = $value_options;
            if(empty($this->value[$k]['parent'])) $this->value[$k]['parent']='null';
 			}
		}
        $this->value =$this->convertToHierarchy($this->value);
	    $this->printMenu($this->value);

    }
    public function printMenu($array,$first=true)
    {
        echo "\n<ol ";
        if($first) { echo "class=\"sortable\""; $first=false; }
        echo ">";
        foreach ($array as $item) {
            if (is_array($item) && isset($item['name'])) {
                $display_options = $item;
                unset($display_options['parent']);
                unset($display_options['childNodes']);
                if (isset($item['childNodes']) && is_array($item['childNodes']) && !empty($item['childNodes'])) {
                    echo "\n<li id='list_" .$item['id']."' >\n<div>";
	                echo '<input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . ']' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-hidden"  value="'.htmlspecialchars(http_build_query($display_options)).'" />';
					echo "" . $item['name']."</div>";
                    $this->printMenu($item['childNodes'],false);
                    echo "\n</li>\n";
                } else {
                    echo "\n<li id='list_" .$item['id']."'>\n<div>";
                    echo '<input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . ']' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-hidden" value="'.htmlspecialchars(http_build_query($display_options)).'" />';
                    echo $item['name'] . "</div>\n</li>\n";
                }
            }
        }
        echo "\n</ol> ";
    }

    function convertToHierarchy($results, $idField='id', $parentIdField='parent', $childrenField='childNodes') {
        $hierarchy = array(); // -- Stores the final data

        $itemReferences = array(); // -- temporary array, storing references to all items in a single-dimention

        foreach ( $results as $item ) {
            $id = $item[$idField];
            $parentId = $item[$parentIdField];

            if (isset($itemReferences[$parentId])) { // parent exists
                $itemReferences[$parentId][$childrenField][$id] = $item; // assign item to parent
                $itemReferences[$id] =& $itemReferences[$parentId][$childrenField][$id]; // reference parent's item in single-dimentional array
            } elseif (!$parentId || !isset($hierarchy[$parentId])) { // -- parent Id empty or does not exist. Add it to the root
                $hierarchy[$id] = $item;
                $itemReferences[$id] =& $hierarchy[$id];
            }
        }

        unset($results, $item, $id, $parentId);

// -- Run through the root one more time. If any child got added before it's parent, fix it.
        foreach ( $hierarchy as $id => &$item ) {
            $parentId = $item[$parentIdField];

            if ( isset($itemReferences[$parentId] ) ) { // -- parent DOES exist
                $itemReferences[$parentId][$childrenField][$id] = $item; // -- assign it to the parent's list of children
                unset($hierarchy[$id]); // -- remove it from the root of the hierarchy
            }
        }

        unset($itemReferences, $id, $item, $parentId);

        return $hierarchy;
    }


    function enqueue()
    {
        wp_enqueue_style(
            'redux-field-cat-sortable-css',
            plugins_url('sortCss.css',__FILE__),
            time(),
            true
        );
        wp_enqueue_script(
            'jquery-nested-cat-sortable-js',
               plugins_url('jquery.mjs.nestedSortable.js',__FILE__),
            array('jquery'),
            time(),
            true
        );

    }
}
