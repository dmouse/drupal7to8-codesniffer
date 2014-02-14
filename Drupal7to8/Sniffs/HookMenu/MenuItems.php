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

class Drupal7to8_Sniffs_HookMenu_MenuItems {

  //Original Drupal7 Menu Items
  protected $menu_items = array();

  //Drupal 8 Route Keys
  protected $menu_yaml_array = '';

  // Module we're working within
  protected $module = '';

  public function __construct($module, array $menu_items) {
    include_once 'menu_constants.inc';

    $this->menu_items = $menu_items;
    $this->module = $module;
    $this->menu_yaml_array = $this->buildRouteYAML();
  }

  protected function formatPathToD8($menu_path) {
    return preg_replace("/%([a-z]+)/", '{$1}', $menu_path);
  }

  public function getArray() {
    return $this->menu_items;
  }

  protected function getYamlKey($path) {
    $menu_key = '';

    if ($this->menu_items[$path]['page callback'] == 'drupal_get_form') {
      //Do some form includy stuff here.
      $callback = explode('_', $this->menu_items[$path]['page arguments'][0]);
      $form_callback = '\Drupal\\'.$this->module.'\Form\\'. Drupal7to8_Utility_CreateFile::camelUnderscores($this->menu_items[$path]['page arguments'][0]);
      $menu_key = preg_replace("/".$this->module."_/", $this->module.".$1", str_replace('_form', '', $this->menu_items[$path]['page arguments'][0]));
    }
    else {
      // FIXME: we probably need to harden this regex.
      $menu_key = preg_replace("/".$this->module."_/", $this->module.".$1", $this->menu_items[$path]['page callback']);
    }
    return $menu_key;
  }

  public function getLocalTasksYAML() {
    // We need to know about our routes before we can build local tasks
    if(empty($this->menu_yaml_array)) {
      return FALSE;
    }

    $local_tasks_yaml = array();
    // For each menu item, we will figure out if they are local tasks and format
    // Accordingly.
    foreach($this->menu_items AS $path => $item) {
      // Only Act on _LOCAL_TASKS
      if(!isset($item['type']) || (
        $item['type'] != MENU_DEFAULT_LOCAL_TASK &&
        $item['type'] != MENU_LOCAL_TASK)) {
        continue;
      }
      $parent = substr($path, 0, strrpos($path, '/'));

      // Default local tasks don't have their own routes.
      if($item['type'] == MENU_DEFAULT_LOCAL_TASK) {
        $components = explode('/', $path);
        $menu_key = array_pop($components);
        $path = $parent;
      }
      else {
        $menu_key = $this->getYamlKey($path);
      }
      $local_tasks_yaml[$menu_key]['route_name'] = $this->getYamlKey($path);
      // If the parent is also a local task, then we're a #2 task and we need a
      // Parent ID instead.
      if (isset($this->menu_items[$parent])) {
        if ($this->menu_items[$parent]['type'] == 'MENU_LOCAL_TASK') {
          $local_tasks_yaml[$menu_key]['parent_id'] = $this->getYamlKey($parent);
        }
        else {
          $local_tasks_yaml[$menu_key]['base_route'] = $this->getYamlKey($parent);
        }
      }
      // If the menu item isn't in this module, the maintainer will have to fill
      // in manually.
      else {
        $local_tasks_yaml[$menu_key]['parent_id'] = 'fixme';
        $local_tasks_yaml[$menu_key]['base_route'] = 'fixme';
      }
      if(isset($item['title'])) {
        $local_tasks_yaml[$menu_key]['title'] = $item['title'];
      }
      if(isset($item['weight'])) {
        $local_tasks_yaml[$menu_key]['weight'] = $item['weight'];
      }
    }

    return $local_tasks_yaml;
  }

  public function buildRouteYAML() {
    $yaml_array = array();
    foreach($this->menu_items AS $path => $item) {
      if(!isset($item['page callback'])) {
        continue;
      }
      $menu_key = $this->getYamlKey($path);
      // Figure out the Router header based off path
      $yaml_array[$menu_key] = array('path' => '/' . $this->formatPathToD8($path), 'defaults' => array(), 'requirements' => array());

      if(isset($item['title'])) {
        $yaml_array[$menu_key]['defaults']['_title'] = $item['title'];
      }

      if(isset($form_callback)) {
        $yaml_array[$menu_key]['defaults']['_form'] = $form_callback;
      }
      else {
        $yaml_array[$menu_key]['defaults']['_content'] = $item['page callback'];
      }
      if(isset($item['access arguments']) && sizeof($item['access arguments']) == 1 && is_string($item['access arguments'][0])) {
        $yaml_array[$menu_key]['requirements']['_permission'] = $item['access arguments'][0];
      }
      if(empty($yaml_array[$menu_key]['requirements'])) {
        unset($yaml_array[$menu_key]['requirements']);
      }

      /* When we can print line-by-line do this:

      if(Drupal7to8_Utility_CreateFile::writeYaml($this->module . 'routing.yml', $yaml_array)) {
        print "File " . $this->module . 'routing.yml' . "Written successfully";
        return TRUE;
      }
      */
    }
    return $yaml_array;
  }

  public function getRouteYAML() {
    return $this->menu_yaml_array;
  }
}