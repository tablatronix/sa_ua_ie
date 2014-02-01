<?php
/*
Plugin Name: sa_x-ua-ie
Description: Sends header to force compatability mode for IE11 to fix cke issues.
Version: 1.0
Author: Shawn Alverson
Author URI: http://www.shawnalverson.com/

*/

$PLUGIN_ID  = "sa_x_ua_ie";
$PLUGINPATH = "$SITEURL/plugins/$PLUGIN_ID/";
$sa_url     ="http://tablatronix.com/getsimple-cms/sa-x-ua-ie/";

$SA_IE_DETECT = 11;
$SA_IE_COMPAT = 9;

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile,                  //Plugin id
	'SA X-UA-IE',               //Plugin name
	'1.0',                      //Plugin version
	'Shawn Alverson',           //Plugin author
	$sa_url,                    //author website
	'Fixes ckeditor in IE11 by forcing compatability mode',  //Plugin description
	'',                         //page type - on which admin tab to display
	''                          //main function (administration)
);

# activate action
add_action('admin-pre-header',$PLUGIN_ID."_action");

# Functions

// if page is edit and browser is IE11 send compatability header for IE 9 or 10
function sa_x_ua_ie_action(){
  Global $SA_IE_DETECT, $SA_IE_COMPAT;
	$DeviceDetection = new DeviceDetection();
	$device = $DeviceDetection->detect();
	if(pageCheck('edit.php') && $device['BROWSER_SHORT'] == 'IE' && $device['BROWSER_VER'] == $SA_IE_DETECT ) {
		header('X-UA-Compatible: IE=' . $SA_IE_COMPAT);    
    // GLOBAL $success;
    // $success .= "IE compatability mode IE" . $SA_IE_COMPAT;
	}; 
}

function pageCheck($page)
{
  return basename($_SERVER['PHP_SELF']) == $page;
}

/**
 * DeviceDetection
 *
 * @author  Timothy Marois <timothymarois@gmail.com>
 *
 */

class DeviceDetection {  

  protected $detected = false;

  protected $v = array(
    'UA'              => '',
    'BROWSER_NAME'    => 'Unknown',
    'BROWSER_VER'     => '0',
    'BROWSER_SHORT'   => 'UNK',
    'DEVICE_OS'       => 'Unknown',
    'DEVICE_CATEGORY' => 'Unknown',
    'LAYOUT_ENGINE'   => 'Unknown'
  );



  public function __construct() {
 
  }

  public function detect($ua="") {

    if ($ua!='') {
      $this->v['UA'] = $ua;
    }
    else {
      $this->v['UA'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    }

    // detect major browsers

    // opera must be above chrome+safri (opera builds off chrome/safari)
    $this->detect_browser_opera();
    // yaBrowser is built off chrome, must be first
    $this->detect_browser_yabrowser();
    // chrome must be before safari (chrome builds off safari)
    $this->detect_browser_chrome();
    $this->detect_browser_firefox();
    // amazon silk must be before safari
    $this->detect_browser_azn_silk();
    $this->detect_browser_safari();
    $this->detect_browser_ie();

    // detect layout engine
    $this->detect_layout_engine();

    // detect operating systems
    $this->detect_os_platform();

    return $this->v;
  }

 

  protected function detect_layout_engine()
  {
    // http://en.wikipedia.org/wiki/List_of_layout_engines
    // todo: google 30+ and opera 15+ will use new engine "Blink"
    // we will ignore the version of the engine for now. possible on todo list?

    // http://en.wikipedia.org/wiki/Trident_(layout_engine)
    // widely microsoft usage
    if (preg_match("/(trident\/)/i", $this->v['UA'],$matches)) {
      $this->v['LAYOUT_ENGINE']  = 'Trident';
      return true;
    } 

    // webkit + applewebkit engine 
    if (preg_match("/(Apple)?WebKit\//i", $this->v['UA'],$matches)) {
      $this->v['LAYOUT_ENGINE']  = 'WebKit';
      return true;
    } 

    // opera from 7-15
    if (preg_match("/(presto\/)/i", $this->v['UA'],$matches)) {
      $this->v['LAYOUT_ENGINE']  = 'Presto';
      return true;
    } 

    // gecko is adapted on all major browsers as "like gecko" we want the firefox version 
    if (preg_match("/(gecko\/)/i", $this->v['UA'],$matches)) {
      $this->v['LAYOUT_ENGINE']  = 'Gecko';
      return true;
    } 
  }



  protected function detect_browser_chrome() {
    if ($this->detected==true) return false;

    if (preg_match("/(chrome)\/([0-9]+)/i", $this->v['UA'],$matches)) {
      $this->v['BROWSER_NAME']  = 'Chrome';
      $this->v['BROWSER_SHORT'] = 'CH';
      $this->v['BROWSER_VER']   = $matches[2];
      $this->detected = true;
      return true;
    } 
    else {
      // detect the iOS chrome version
      if (preg_match("/(CriOS)\/([0-9]+)/i", $this->v['UA'],$matches)) {
        $this->v['BROWSER_NAME']  = 'Chrome';
        $this->v['BROWSER_SHORT'] = 'CH';
        $this->v['BROWSER_VER']   = $matches[2];
        $this->detected = true;
        return true;
      }
    } 

    return false;
  }


  protected function detect_browser_firefox() {
    if ($this->detected==true) return false;

    if (preg_match("/(firefox)\/([0-9]+)/i", $this->v['UA'],$matches)) {
      $this->v['BROWSER_NAME']  = 'Firefox';
      $this->v['BROWSER_SHORT'] = 'FF';
      $this->v['BROWSER_VER']   = $matches[2];
      $this->detected = true;
      return true;
    } 

    return false;
  }
  
  
  // added support for YaBrowser - Yandex browser (Russia)
  // http://browser.yandex.com/
  protected function detect_browser_yabrowser() {
    if ($this->detected==true) return false;

    if (preg_match("/(yabrowser)\/([0-9]+)/i", $this->v['UA'],$matches)) {
      $this->v['BROWSER_NAME']  = 'Yandex';
      $this->v['BROWSER_SHORT'] = 'YA';
      $this->v['BROWSER_VER']   = $matches[2];
      $this->detected = true;
      return true;
    } 

    return false;
  }


  protected function detect_browser_safari() {
    if ($this->detected==true) return false;

    if (preg_match("/(safari)\/([0-9]+)/i", $this->v['UA'],$matches)) {
      $this->v['BROWSER_NAME']  = 'Safari';
      $this->v['BROWSER_SHORT'] = 'SF';
      $this->v['BROWSER_VER']   = $matches[2];
      $this->detected = true;
      return true;
    } 

    return false;
  }
  
  // added Support for Amazon Silk (it is made for Kindle Fire) = Tablet 
  // http://en.wikipedia.org/wiki/Amazon_Silk
  protected function detect_browser_azn_silk() {
    if ($this->detected==true) return false;

    if (preg_match("/(silk)\/([0-9]+)/i", $this->v['UA'],$matches)) {
      $this->v['BROWSER_NAME']    = 'Amazon Silk';
      $this->v['BROWSER_SHORT']   = 'SK';
      $this->v['BROWSER_VER']     = $matches[2];
      $this->v['DEVICE_CATEGORY'] = 'Tablet';
      $this->detected = true;
      return true;
    } 

    return false;
  }


  protected function detect_browser_ie() {
    if ($this->detected==true) return false;

    // detects IE:6,7,8,9, and 10
    if (preg_match("/(msie) ([0-9]+)/i", $this->v['UA'],$matches)) {
      $this->v['BROWSER_NAME']  = 'Internet Explorer';
      $this->v['BROWSER_SHORT'] = 'IE';
      $this->v['BROWSER_VER']   = $matches[2];
      $this->detected = true;
      return true;
    } 
    else
    {
      // detection for IE11+ (removal of MSIE)
      // http://msdn.microsoft.com/en-us/library/ie/bg182625(v=vs.85).aspx
      if (preg_match("/trident/i",$this->v['UA']) && preg_match("/like gecko/i",$this->v['UA'])) {
        if (preg_match("/rv:([0-9]+)/i",$this->v['UA'],$matches)) {
          $this->v['BROWSER_NAME']  = 'Internet Explorer';
          $this->v['BROWSER_SHORT'] = 'IE';
          $this->v['BROWSER_VER']   = $matches[1];
          $this->detected = true;
          return true;
        }
      } 
    }

    return false;
  }


  protected function detect_browser_opera() {
    if ($this->detected==true) return false;

    // newest version of Opera
    if (preg_match("/(OPR)\/([0-9]+)/i", $this->v['UA'],$matches)) {
      $this->v['BROWSER_NAME']  = 'Opera';
      $this->v['BROWSER_SHORT'] = 'OP';
      $this->v['BROWSER_VER']   = $matches[2];
      $this->detected = true;
      return true;
    } 
    else {
      // detect older versions of opera
      if (preg_match("/presto\//i",$this->v['UA'])) {
        if (preg_match("/(opera)\/([0-9]+)/i", $this->v['UA'],$matches)) {
          $this->v['BROWSER_NAME']  = 'Opera';
          $this->v['BROWSER_SHORT'] = 'OP';
          $this->v['BROWSER_VER']   = $matches[2];
          $this->detected = true;
          return true;
        } 
      } 
    }

    return false;
  }



  protected function detect_os_platform() {
    if (preg_match("/(windows phone)/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'Windows';
      $this->v['DEVICE_CATEGORY']   = 'Mobile';
      return true;
    } 

    if (preg_match("/(iemobile)/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_CATEGORY']   = 'Mobile';
    } 

    if (preg_match("/(windows)/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'Windows';
      $this->v['DEVICE_CATEGORY']   = 'Desktop';
      return true;
    } 

    // added support for Chrome OS
    if (preg_match("/( CrOS )/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'ChromeOS';
      $this->v['DEVICE_CATEGORY']   = 'Desktop';
      return true;
    } 

    if (preg_match("/(android [0-9])/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'Android';
      $this->v['DEVICE_CATEGORY']   = 'Mobile';
      return true;
    } 

    if (preg_match("/(iphone;)/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'iOS';
      $this->v['DEVICE_CATEGORY']   = 'Mobile';
      return true;
    } 

    if (preg_match("/(ipad;)/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'iOS';
      $this->v['DEVICE_CATEGORY']   = 'Tablet';
      return true;
    } 

    if (preg_match("/(macintosh;)/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'Macintosh';
      $this->v['DEVICE_CATEGORY']   = 'Desktop';
      return true;
    } 

    if (preg_match("/(mac os)/i", $this->v['UA'],$matches)) {
      $this->v['DEVICE_OS']         = 'Macintosh';
      $this->v['DEVICE_CATEGORY']   = 'Desktop';
      return true;
    } 

    return false;
  }



}
