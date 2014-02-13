<?php
/**
 * @file MenuItem.php
 * This class creates a menuitem object
 * User: japerry
 * Date: 2/13/14
 * Time: 10:18 AM
 */
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;

class Drupal7to8_Sniffs_HookMenu_MenuItem {

  protected $menu_item = array();
  protected $menu_path = '';
  protected $module = '';

  public function __construct($module, $menu_path, array $menu_item) {
    $this->menu_path = $this->formatPathToD8($menu_path);
    $this->menu_item = $menu_item;
    $this->module = $module;
  }

  protected function formatPathToD8($menu_path) {
    return preg_replace("/%([a-z]+)/", '{$1}', $menu_path);
  }

  public function getArray() {
    return $this->menu_item;
  }

  public function getPath() {
    return $this->menu_path;
  }

  public function getRouteYAML() {
    $path = explode('/', $this->menu_path);
    if(!isset($this->menu_item['page callback'])) {
      return;
    }
    if ($this->menu_item['page callback'] == 'drupal_get_form') {
      //Do some form includy stuff here.
      $callback = explode('_', $this->menu_item['page arguments'][0]);
      $form_callback = '\Drupal\\'.$this->module.'\Form\\'. Drupal7to8_Utility_CreateFile::camelUnderscores($this->menu_item['page arguments'][0]);
      $route_header = preg_replace("/".$this->module."_/", $this->module.".$1", str_replace('_form', '', $this->menu_item['page arguments'][0]));
    }
    else {
      $route_header = preg_replace("/".$this->module."_/", $this->module.".$1", $this->menu_item['page callback']);
    }
    // Figure out the Router header based off path

    $yaml_array = array(
      $route_header => array('path' => '/' . $this->menu_path, 'defaults' => array(), 'requirements' => array()),
    );

    if(isset($this->menu_item['title'])) {
      $yaml_array[$route_header]['defaults']['_title'] = $this->menu_item['title'];
    }

    if(isset($form_callback)) {
      $yaml_array[$route_header]['defaults']['_form'] = $form_callback;
    }
    else {
      $yaml_array[$route_header]['defaults']['_content'] = $this->menu_item['page callback'];
    }
    if(isset($this->menu_item['access arguments']) && sizeof($this->menu_item['access arguments']) == 1 && is_string($this->menu_item['access arguments'][0])) {
      $yaml_array[$route_header]['requirements']['_permission'] = $this->menu_item['access arguments'][0];
    }
    if(empty($yaml_array[$route_header]['requirements'])) {
      unset($yaml_array[$route_header]['requirements']);
    }

    /* When we can print line-by-line do this:

    if(Drupal7to8_Utility_CreateFile::writeYaml($this->module . 'routing.yml', $yaml_array)) {
      print "File " . $this->module . 'routing.yml' . "Written successfully";
      return TRUE;
    }
    */
    return $yaml_array;
  }
}