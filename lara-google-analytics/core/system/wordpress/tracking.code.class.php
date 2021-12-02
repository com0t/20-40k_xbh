<?php
namespace Lara\Widgets\GoogleAnalytics;

/**
 * @package    Google Analytics by Lara
 * @author     Amr M. Ibrahim <mailamr@gmail.com>
 * @link       https://www.xtraorbit.com/
 * @copyright  Copyright (c) XtraOrbit Web development SRL 2016 - 2020
 */

if (!defined("ABSPATH"))
    die("This file cannot be accessed directly");

class TrackingCode {
	private static $initialized  = false;
	
	public static function initInstance() {

		if (self::$initialized){
			return;
		}

		self::$initialized  = true;
    }
	
	private static function is_analytics($str){
		return (bool) preg_match('/^ua-\d{4,20}(-\d{1,10})?$/i', $str);
	}
	

	private static function get_settings(){
		global $wpdb;
		$results = array();
		$sql = $wpdb->prepare ( "SELECT `name`, `value` FROM  `".lrgawidget_plugin_table."`  WHERE `name` = %s ", array("settings" ));	
		$result = $wpdb->get_row( $sql , ARRAY_A );
		if ((empty($wpdb->last_error)) && is_array($result) && !empty($result["value"])){
			$results = json_decode($result["value"], true);
		}
		return $results;
	}
	
	public static function get_ga_code(){
		$settings = self::get_settings();
		if (!empty($settings["enable_universal_tracking"]) && $settings["enable_universal_tracking"] === "on"){
			if(!empty($settings["property_id"]) && self::is_analytics($settings["property_id"])){		
				$property_id = $settings["property_id"];
?>

<!-- Lara's Google Analytics - https://www.xtraorbit.com/wordpress-google-analytics-dashboard-widget/ -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $property_id ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo $property_id ?>', { 'anonymize_ip': true });
</script>

<?php
			}
		}
	}	
}
TrackingCode::initInstance();
?>