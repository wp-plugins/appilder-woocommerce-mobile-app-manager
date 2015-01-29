<?php
class ReduxFramework_nav_menu_builder
{
     var $types = array("cat"=>"Category","product"=>"Product","inapp"=>"In-App Page","wlink"=>"WebView link","elink"=>"External link","title"=>"Title");
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
        include_once(ReduxFramework::$_dir. "inc/fields/media/field_media.php");
        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'id',
            'show_count' => true,
            'pad_counts' => true,
            'hierarchical' => true,
            'title_li' => '',
            'hide_empty' => false
        );
        $catTerms = get_categories($args);
        $sortable = array();
        foreach($catTerms as $cat){
            $sortable[] = array(
                'id'=> 'cat_'.$cat->term_id,
                'parent'=>'cat_'.$cat->parent,
                'label' => $cat->cat_name,
                'label_value' => $cat->cat_name,
                'type' => 'cat',
                'value' => $cat->term_id,
                'taxonomy'=>$cat->taxonomy
            );
        }
        $this->field['options'] = $sortable;
        $this->widget_handler = new widget_handler($this->field,$this->value,$this->parent);
        $this->media_field = new ReduxFramework_media(array(
            'id'       => $this->field['id'],
            'class'       => 'opt-media',
            'name'       => $this->field['id'],
            'name_suffix'       => '',
            'type'     => 'media',
            'width'    => '100',
            'height'    => '100',
            'url'      => false,
            'title'    => __('Media w/ URL', 'redux-framework-demo'),
            'desc'     => __('Basic media uploader with disabled URL input field.', 'redux-framework-demo'),
            'subtitle' => __('Upload any media using the WordPress native uploader', 'redux-framework-demo'),
        ),'',$this->parent);
    }
    function getType($key){
        return isset($this->types[$key])?$this->types[$key]:'';
    }
    function items(){
        ?>
        <div style="float: right;width: 35%;padding: 8px 21px 0px 6px;border: 1px solid #ccc;background: #F9F9F9" data-name = "<?php echo $this->field['name']; ?>" data-id = "<?php echo $this->field['id']; ?>">
            <h3>Menu Items  <br><span style="font-size: small;font-weight: normal">Add required menu items to navigation menu</span>
            </h3>
            <div class="page_builder_acc active" data-type="title">
                <h3>Title</h3>
                <div>
                    <label>Title</label>
                    <input type="text" name="value" class="label addfield" value=""/>
	                <input type="submit" style="display:block;" class="addtocat button button-primary" value="Add"/>
                </div>
            </div>
            <div class="page_builder_acc redux-container-media uploadfield" data-type="cat">
                <h3>Categories</h3>
                <div>
                    <label>Icon</label>
                    <?php   $this->media_field->render(); ?>
                    <label>Category</label>
                    <?php $this->widget_handler->widget_field(widget_handler::$category_selector,false,array("class"=>"select2 addfield")); ?>
                     <label style="display:block">Label</label>
                    <input type="text" name="label" class="label" value=""/>
	                <input type="submit" style="display:block;" class="addtocat button button-primary" value="Add"/>
                </div>
            </div>
            <div class="page_builder_acc redux-container-media uploadfield" data-type="inapp">
                <h3>In-App Pages</h3>
                <div>
                    <label>Icon</label>
                    <?php   $this->media_field->render(); ?>
                    <label>In-App Page</label>
                    <?php $this->widget_handler->widget_field(widget_handler::$inApp_selector,false,array("class"=>"select2 addfield")); ?>
	                <label style="display:block">Label</label>
	                <input type="text" name="label" class="label" value=""/>
	                <input type="submit" style="display:block;" class="addtocat button button-primary" value="Add"/>
                </div>
            </div>
            <div class="page_builder_acc redux-container-media uploadfield" data-type="product">
                <h3>Products</h3>
                <div>
                    <label>Icon</label>
                    <?php   $this->media_field->render(); ?>
                    <label>Product</label data-type="cat">
                    <?php $this->widget_handler->widget_field(widget_handler::$product_selector,false,array("class"=>"select2 addfield")); ?>
	                <label style="display:block">Label</label>
                    <input type="text" name="label" class="label" value=""/>
	                <input type="submit" style="display:block;" class="addtocat button button-primary" value="Add"/>
                </div>
            </div>
            <div class="page_builder_acc redux-container-media uploadfield" data-type="wlink">
                <h3>WebView Link</h3>
                <div>
                    <label>Icon</label>
                    <?php   $this->media_field->render(); ?>
                    <label style="display:block;">URL</label>
                    <input type="text" class="addfield">
	                <label style="display:block">Label</label>
                    <input type="text" name="label" class="label" value=""/>

	                <input type="submit" style="display:block;" class="addtocat button button-primary" value="Add"/>
                </div>
            </div>
            <div class="page_builder_acc redux-container-media uploadfield" data-type="elink">
                <h3>External Link</h3>
                <div>
                    <label>Icon</label>
                    <?php   $this->media_field->render(); ?>
                    <label style="display:block;">URL</label>
                    <input type="text" class="addfield">
	                <label style="display:block">Label</label>
                    <input type="text" name="label" class="label" value=""/>
	                <input type="submit" style="display:block;" class="addtocat button button-primary" value="Add"/>
                </div>
            </div>
        </div>
        <?php
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
        if(isset($this->field['doc']))
            echo $this->field['doc'];
       ?>
        </fieldset></td></tr>
        <tr>
        <td colspan="2">
        <fieldset id="mobappSettings-nav_menu" class="redux-field-container redux-field redux-container-nav_menu_builder redux-field-init" data-id="nav_menu" data-type="nav_menu_builder">
            <div style="float: left;width:100%;padding: 8px 21px 10px 6px;border: 1px solid #ccc;background: #F9F9F9" class="nav_menu_items_space">
        <?php
        if(empty($this->value) || !is_array($this->value)){
            $this->value=$this->field['options'];
        }
        $this->value =$this->convertToHierarchy($this->value);
        echo '<div style="float: left;width:55%;padding: 8px 21px 10px 6px;border: 1px solid #ccc;background: #F9F9F9" class="nav_menu_items_space">';
        echo '<h3>Navigation Menu<br><span style="font-size: small;font-weight: normal">Drag and drop elements to change hierarchy</span></h3>';
	    $this->printMenu($this->value);
        echo '</div>';
        $this->items();
        ?>
                </div>
         </fieldset>
        </td>
        </tr>
       <?php
    }
    public function printMenu($array,$first=true)
    {
        echo "\n<ol ";
        if($first) { echo "class=\"sortable\""; $first=false; }
        echo ">";
        foreach ($array as $item) {
            if (is_array($item) && isset($item['id'])) {
                $display_options = $item;
                unset($display_options['parent']);
                unset($display_options['childNodes']);
                if (isset($item['childNodes']) && is_array($item['childNodes']) && !empty($item['childNodes'])) {
                    echo "\n<li id='list_" .$item['id']."' >\n<div class='page_builder_acc'><h3>".$item['label'] ."</h3><div>";
	              //  echo '<input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . ']' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-hidden"  value="'.htmlspecialchars(http_build_query($display_options)).'" />';
                    if(isset($item['media_id']) && isset($item['media_url']) && !empty($item['media_url']) &&!empty($item['media_id']))
                    {
                        echo '<img src="'.$item['media_url'].'" style="width:32px;height:32px;" /><br>';
                        echo '<input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][media_url]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-value"  value="'.$item['media_url'].'"/>
                              <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][media_id]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-label_value"  value="'.$item['media_id'].'"/>';

                    }
				    echo '<label>Label</label>
                    <input type="text" class="labeledit" name="' . $this->field['name'] . '[' . $item['id'] . '][label]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-label"  value="'.$item['label'].'"/>
                    <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][value]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-value"  value="'.$item['value'].'"/>
                    <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][label_value]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-label_value"  value="'.$item['label_value'].'"/>
                    <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][type]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-type"  value="'.$item['type'].'"/>
			        <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][id]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-id"  value="'.$item['id'].'"/>
			        <input type="hidden" class="parentfield" name="' . $this->field['name'] . '[' . $item['id'] . '][parent]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-parent"  value="'.$item['parent'].'"/>';
                    $dis_val = (isset($item['label_value']))?$item['label_value']:$item['value'];
                    echo '<div class="bt_info_txt"><span class="disp">'.$dis_val.' ('.$this->getType($item['type']).')</span> <span class="del"><a href="#" class="deleteNavItem">Delete</a></span></div>';
                    echo "</div></div>";
                    $this->printMenu($item['childNodes'],false);
                    echo "\n</li>\n";
                } else {
                    echo "\n<li id='list_" .$item['id']."'>\n<div class='page_builder_acc'><h3>".$item['label'] ."</h3><div>";

                    //echo '<input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . ']' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-hidden" value="'.htmlspecialchars(http_build_query($display_options)).'" />
                    if(isset($item['media_id']) && isset($item['media_url']) && !empty($item['media_url']) &&!empty($item['media_id']))
                    {
                        echo '<img src="'.$item['media_url'].'" style="width:32px;height:32px;" /><br>';
                        echo '<input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][media_url]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-value"  value="'.$item['media_url'].'"/>
                              <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][media_id]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-label_value"  value="'.$item['media_id'].'"/>';
                    }
                    echo '<label>Label</label>
                        <input type="text" class="labeledit" name="' . $this->field['name'] . '[' . $item['id'] . '][label]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-label"  value="'.$item['label'].'"/>
                        <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][value]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-value"  value="'.$item['value'].'"/>
                        <input type="hidden"  name="' . $this->field['name'] . '[' . $item['id'] . '][label_value]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-label_value"  value="'.$item['label_value'].'"/>
                        <input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][type]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-type"  value="'.$item['type'].'"/>
            			<input type="hidden" name="' . $this->field['name'] . '[' . $item['id'] . '][id]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-id"  value="'.$item['id'].'"/>
			            <input type="hidden" class="parentfield" name="' . $this->field['name'] . '[' . $item['id'] . '][parent]' . $this->field['name_suffix'] . '" id="' . $this->field['id'].'-' . $item['id'].'-parent"  value="'.$item['parent'].'"/>';
                        $dis_val = (isset($item['label_value']) && !empty($item['label_value']))?$item['label_value']:$item['value'];
                    echo '<div class="bt_info_txt"><span class="disp">'.$dis_val.' ('.$this->getType($item['type']).')</span> <span class="del"><a href="#" class="deleteNavItem">Delete</a></span></div>';
                    echo "</div></div>\n</li>\n";
                }
            }
        }
        echo "\n</ol>";
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
        wp_enqueue_style(
            'redux-field-nav-build-css',
            plugins_url('nav_menu_builder.css',__FILE__),
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
        wp_enqueue_script(
            'jquery-nav-builder-js',
            plugins_url('nav_menu_builder.js',__FILE__),
            array('jquery'),
            time(),
            true
        );

    } }

