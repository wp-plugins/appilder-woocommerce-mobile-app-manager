<?php
/**
 * User: Mohammed Anees
 * Date: 8/2/14
 */
class WOOAPP_API_Widgets extends WOOAPP_API_Resource {
    public function register_routes($routes){
        return $routes;
    }
    public function getWidgets($wid=false){
        $widgets = array(
            'slider_widgets'=>"Slider_Widgets",
            'grid_widgets'=>"Grid_Widgets",
            'search_widgets'=>"Search_Widgets",
            'product_scroller_widgets'=>"Product_Scroller",
            'banner_widgets'=>"Banner_Widgets",
            'menu_widgets'=>"Menu_Widgets",
            'html_widgets'=>"Html_Widgets",
        );
        if($wid==false)
            return $widgets;
        else
            return isset($widgets[$wid])?$widgets[$wid]:false;
    }
    public function  getWidgetsOfPage($page,$getValues = true){
            global $mobappSettings;
            // print_r($mobappSettings);
            if(isset($mobappSettings['page_layout_'.$page]['enabled'])){
                $widgets = $mobappSettings['page_layout_'.$page]['enabled'];
            }
            $widgets  = array_filter($widgets,array($this,"filter_widget_name"));
            if($getValues == false)
                $widgets = array_keys($widgets);
            else{
                foreach($widgets as $mixed=>$widget){
                    $widget = $this->getWidgetMeta($mixed);
                    unset($widgets[$mixed]);
                    if($widget!==false && array_key_exists($widget['widget'],$this->getWidgets())){
                        $item = $widget['widget'];
                        $id = $widget['id'];
                        require_once("app-widgets/".$widget['widget'].".php");
                        $widget = $this->getWidgets($item);
                        $widget  = new $widget();
                        $temp = $widget->getValueById($id,$item);
                        if(!empty($temp) && $temp!==false)
                            $widgets[] = $temp;
                    }

                }
            }
            return $widgets;
    }
    public function filter_widget_name($name){
        return ($name!="placebo");
    }
    public function getWidgetMeta($mixed){
        if(preg_match("/([a-zA-Z_]+)[_]([0-9]+)[_]([0-9]+)/i",$mixed,$metas)) {
            $return = array();
            $return['widget'] = $metas[1];
            $return['id'] = $metas[2];
            $return['count'] = $metas[3];
        }elseif(preg_match("/([a-zA-Z_]+)[_]([a-z]+)/i",$mixed,$metas)){
            $return = array();
            $return['widget']=$metas[1];
            $return['id']=$metas[2];
            $return['count']= 0;
        }else {
            $return = false;
        }
        return $return;
    }
}