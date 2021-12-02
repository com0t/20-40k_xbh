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

class LaraGoogleAnalyticsWidget {
	
	private $systemTimeZone;
    private $gapi;
	private $cache;
	private $cacheOutput;
	private $cachedOutput;
	private $cachePrefix;
	private $cacheTime;
	private $dParams  = array();
	private $params   = array();
	private $filters  = array();	
	private $settings = array();
	private $results  = array();
	private $output   = array();
	private $errors   = array();	
	private $cached;
	private $currentQueryParams;
	private $mdCurrentQueryParams;
	private $calculateTotalsFor;
	private $cacheEarningsOutput;
	private $earningsCacheTime;
	
	function __construct(){
		$this->systemTimeZone = SystemBootStrap::getSystemTimeZone();
		$this->gapi = new GoogleAnalyticsAPI();
		$this->cache = true;
		$this->cacheOutput = true;
		$this->cachePrefix = "lrga_";
		$this->cacheTime = 3600;
		$this->dParams = array( 'metrics' => 'ga:sessions', 'sort' => '-ga:sessions');
		$this->calculateTotalsFor = "ga:sessions";
		$this->cacheEarningsOutput = true;
		$this->earningsCacheTime = 900;
	}
	
	private function getGraphObject(){
		$graphObject = false;
		if (in_array("ecom_woo", DataStore::$RUNTIME["permissions"])){
			if (class_exists( 'woocommerce' )){
				require(lrgawidget_plugin_dir . "core/plugins/earnings/lrga_earnings_sales.php");
				require(lrgawidget_plugin_dir . "core/plugins/earnings/wordpress/lrga_wp_woo_plugin.php");
				$graphObject = new lrga_wp_woo_plugin($this->get_session_setting('start_date'), $this->get_session_setting('end_date'));
			}
		}
		return $graphObject;
	}
	
	public function getSessions(){
		$this->params = array('metrics' => 'ga:sessions,ga:users,ga:pageviews, ga:percentNewSessions,ga:bounceRate,ga:avgSessionDuration,ga:pageviewsPerSession',
		                      'dimensions' => 'ga:date',
							  'sort' => 'ga:date');
		$this->doCall();
		
		$cachedCall = array();
        if (($this->cacheOutput === true) && (!empty($this->mdCurrentQueryParams))){
            $cachedCall = DataStore::get_from_cache($this->cachePrefix, $this->mdCurrentQueryParams."_output", $this->cacheTime);
		}
			
		if (!empty($cachedCall)){
			$this->output = $cachedCall;
			$this->cachedOutput = true;
		}else{
			@array_walk($this->results['rows'], array($this, 'convertDate'));
			$plotData = array(); 
			foreach ($this->results['rows'] as $row){
				foreach ($row as $id => $value){
					if     ($id === 1){$plotData['sessions'][] = array($row[0], $value); $plotData['sessions_sb'][] = $value;}
					elseif ($id === 2){$plotData['users'][] = array($row[0], $value); $plotData['users_sb'][] = $value;}
					elseif ($id === 3){$plotData['pageviews'][] = array($row[0], $value); $plotData['pageviews_sb'][] = $value;}
					elseif ($id === 4){$plotData['percentNewSessions'][] = array($row[0], $this->roundNumbers($value)); $plotData['percentNewSessions_sb'][] = $this->roundNumbers($value);}
					elseif ($id === 5){$plotData['bounceRate'][] = array($row[0], $this->roundNumbers($value)); $plotData['bounceRate_sb'][] = $this->roundNumbers($value);}
					elseif ($id === 6){$plotData['avgSessionDuration'][] = array($row[0],$this->roundNumbers($value)); $plotData['avgSessionDuration_sb'][] = $this->roundNumbers($value);}
					elseif ($id === 7){$plotData['pageviewsPerSession'][] = array($row[0], $this->roundNumbers($value)); $plotData['pageviewsPerSession_sb'][] = $this->roundNumbers($value);}
				}
			}
			$finalPlotData['sessions'] = array("label" => __('Sessions', 'lara-google-analytics'), "data" => $plotData['sessions'], "lrbefore"=>"", "lrafter"=>"", "lrformat"=>"");
			$finalPlotData['users'] = array("label" => __('Users', 'lara-google-analytics'), "data" => $plotData['users'], "lrbefore"=>"", "lrafter"=>"", "lrformat"=>"");
			$finalPlotData['pageviews'] = array("label" => __('Pageviews', 'lara-google-analytics'), "data" => $plotData['pageviews'], "lrbefore"=>"", "lrafter"=>"", "lrformat"=>"");
			$finalPlotData['percentNewSessions'] = array("label" => __('% New Sessions', 'lara-google-analytics'), "data" => $plotData['percentNewSessions'], "lrbefore"=>"", "lrafter"=>"%", "lrformat"=>"");
			$finalPlotData['bounceRate'] = array("label" => __('Bounce Rate', 'lara-google-analytics'), "data" => $plotData['bounceRate'], "lrbefore"=>"", "lrafter"=>"%", "lrformat"=>"");
			$finalPlotData['avgSessionDuration'] = array("label" => __('Avg. Session Duration', 'lara-google-analytics'), "data" => $plotData['avgSessionDuration'], "lrbefore"=>"", "lrafter"=>"", "lrformat"=>"seconds");
			$finalPlotData['pageviewsPerSession'] = array("label" => __('Pages / Session', 'lara-google-analytics'), "data" => $plotData['pageviewsPerSession'], "lrbefore"=>"", "lrafter"=>"", "lrformat"=>"");
			
			$totalsForAllResults['sessions'] = array("total" => $this->results['totalsForAllResults']['ga:sessions'], "data" => implode(",", $plotData['sessions_sb']));
			$totalsForAllResults['users'] = array("total" => $this->results['totalsForAllResults']['ga:users'], "data" => implode(",", $plotData['users_sb']));
			$totalsForAllResults['pageviews'] = array("total" => $this->results['totalsForAllResults']['ga:pageviews'], "data" => implode(",", $plotData['pageviews_sb']));
			$totalsForAllResults['percentNewSessions'] = array("total" => $this->roundNumbers($this->results['totalsForAllResults']['ga:percentNewSessions']), "data" => implode(",", $plotData['percentNewSessions_sb']));
			$totalsForAllResults['bounceRate'] = array("total" => $this->roundNumbers($this->results['totalsForAllResults']['ga:bounceRate']), "data" => implode(",", $plotData['bounceRate_sb']));
			$totalsForAllResults['avgSessionDuration'] = array("total" => gmdate("H:i:s", $this->results['totalsForAllResults']['ga:avgSessionDuration']), "data" => implode(",", $plotData['avgSessionDuration_sb']));
			$totalsForAllResults['pageviewsPerSession'] = array("total" => $this->roundNumbers($this->results['totalsForAllResults']['ga:pageviewsPerSession']), "data" => implode(",", $plotData['pageviewsPerSession_sb']));

			$this->output =  array ("plotdata" => $finalPlotData, "totalsForAllResults" =>$totalsForAllResults);

			if (($this->cacheOutput === true) && (!empty($this->mdCurrentQueryParams))){
				DataStore::save_to_cache($this->cachePrefix, $this->mdCurrentQueryParams."_output", $this->output);
			}
		}
		$this->getEarnings();
		$this->jsonOutput();
	}
	
	
	private function getEarnings(){
		if ($this->get_database_setting('enable_ecommerce_graph') === "on"){
			$graphOutput  = array();
			if ($this->cacheEarningsOutput === true){
				$graphOutput = DataStore::get_from_cache($this->cachePrefix,"earnings_seriesData", $this->earningsCacheTime);
			}
			if (empty($graphOutput)){
				$graphObject = $this->getGraphObject();
				if (($graphObject !== false) && (is_object($graphObject))){
					$graphOutput = $graphObject->getGraphOutput();
					if ($this->cacheEarningsOutput === true){
						DataStore::save_to_cache($this->cachePrefix, "earnings_seriesData", $graphOutput);
					}
				}
			}

			if (!empty($graphOutput)){
				list($this->output['plotdata']['sales'], $this->output['plotdata']['earnings'],$this->output['graph']['settings']) = $graphOutput;
			}
		}
	}
	
	public function getGraphData(){
		if ($this->get_database_setting('enable_ecommerce_graph') === "on"){
			$graphData  = array();
			if ($this->cacheEarningsOutput === true){
				$graphData = DataStore::get_from_cache($this->cachePrefix,"earnings_graphData", $this->earningsCacheTime);
				$cached = true;
			}
			
			if (empty($graphData)){
				$cached = false;
				$graphObject = $this->getGraphObject();
				if (($graphObject !== false) && (is_object($graphObject))){
					$graphData = $graphObject->getGraphData();
					DataStore::save_to_cache($this->cachePrefix, "earnings_graphData", $graphData);
				}
			}
			
			if (!empty($graphData)){
				$this->output = $graphData;
			}
			$this->output['gaoptionscached'] = $cached;
		}
		$this->jsonOutput();
	}	
	
	public function getBrowsers($lrdata){
		$this->params = array( 'dimensions' => 'ga:browser');
		$this->doCall(true);
	}

	public function getLanguages(){
		$this->params = array('dimensions' => 'ga:language');		
		$this->doCall(true);
	}

	public function getOS($lrdata){
		$this->params = array( 'dimensions' => 'ga:operatingSystem');
		$this->doCall(true);
	}
	
	public function getDevices($lrdata){
		$this->params = array( 'dimensions' => 'ga:deviceCategory');
		$this->doCall(false);
		
		$this->calculateTotals();
		$this->jsonOutput();
		
	}	

	public function getScreenResolution(){
		$this->params = array('dimensions' => 'ga:ScreenResolution');		
		$this->doCall(true);
	}	

	public function getPages(){
		$this->params = array('metrics' => 'ga:pageviews', 'dimensions' => 'ga:pagePath,ga:pageTitle,ga:hostname', 'sort' => '-ga:pageviews');
		$this->calculateTotalsFor = "ga:pageviews";
		$this->doCall(false);
		
		if (!empty($this->results['rows']) && is_array($this->results['rows'])){
			@array_walk($this->results['rows'], array($this, 'preparePagesOutput'));
		}
		
		$this->calculateTotals();
		$this->jsonOutput();		
	}
	
	private function doCall($handleOutput=false){
		$this->checkSettings();
		$_params = array_merge($this->dParams, $this->params, $this->filters);
		$this->gapi->buildQuery($_params);
		$this->setCurrentQueryParms();
		$this->inCache($this->currentQueryParams);
		if (!$this->cached){
			$this->results = $this->gapi->doQuery();
			if ($this->cache){
				DataStore::save_to_cache($this->cachePrefix, $this->mdCurrentQueryParams, $this->results);
			}
		}
		if ($handleOutput){
			$this->calculateTotals();
			$this->jsonOutput();
		}
	}
	
	private function inCache($query){
        $this->cached = false; 		
		if ($this->cache){
			$queryID = md5(json_encode($query, true));
			$cachedCall = DataStore::get_from_cache($this->cachePrefix, $queryID, $this->cacheTime);
			if (!empty($cachedCall)){
				$this->results = $cachedCall;
				$this->cached = true;
			}
	    }
	}

	private function setCurrentQueryParms(){
		$this->currentQueryParams = $this->gapi->getQueryParams();
		$this->mdCurrentQueryParams = md5(json_encode($this->currentQueryParams, true));
	}
	
	private function checkSettings (){
		if ( ($this->get_database_setting('client_id') === null) || ($this->get_database_setting('client_secret') === null) || ($this->get_database_setting('access_token')=== null) || ($this->get_database_setting('profile_id')=== null)){
			$this->output = array("setup" => 1);
			$this->jsonOutput();
		}
		if ( ($this->get_session_setting('start_date') !== null) && ($this->get_session_setting('end_date') !== null)){
			$this->setGapiValues(array( 'start_date'   => $this->get_session_setting('start_date'), 
										'end_date'     => $this->get_session_setting('end_date')));
		}
		$this->setGapiValues(array('profile_id'   => $this->get_database_setting('profile_id')));
		$this->refreshToken();		
	}

    ## Authentication Methods	
	public function getAuthURL($lrdata){
		$this->setGapiValues(array( 'client_id' => $lrdata['client_id'], 'client_secret'  => $lrdata['client_secret']));
		$this->output = array('url'=>$this->gapi->authURL());
		$this->jsonOutput();
	}
	
	public function getProfiles(){
		$this->refreshToken();
		$this->results = $this->gapi->getAccounts();
		$this->output['all_accounts'] = $this->results['items'];
		$this->results = $this->gapi->getProfiles(array('fields' => 'items(id,timezone)'));
		$this->output['all_profiles'] = $this->results['items'];

		DataStore::save_to_cache($this->cachePrefix, md5('all_accounts_and_profiles')."_output", $this->output);
		
		$this->output['current_selected'] = array("account_id"         => $this->get_database_setting('account_id'),
		                                           "property_id"       => $this->get_database_setting('property_id'),
												   "profile_id"        => $this->get_database_setting('profile_id'),
												   "profile_timezone"  => $this->get_database_setting('profile_timezone'),
												   "lock_settings"     => $this->get_database_setting('lock_settings')); 
		$this->jsonOutput();
	}

	public function getAccessToken($lrdata){
		if ($lrdata['client_id'] === lrgawidget_plugin_client_id){$lrdata['client_secret'] = lrgawidget_plugin_client_secret;}
		$this->setGapiValues(array( 'client_id' => $lrdata['client_id'], 'client_secret'  => $lrdata['client_secret'], 'code' => $lrdata['code']));
		$results = $this->gapi->getAccessToken();
		$this->set_database_setting(array('client_id'     => $lrdata['client_id'],
										  'client_secret' => $lrdata['client_secret'],
										  'access_token'  => $results['access_token'],
										  'token_type'    => $results['token_type'],
										  'expires_in'    => $results['expires_in'],
										  'refresh_token' => $results['refresh_token'],
										  'created_on'    => time()));
		$this->jsonOutput();
	}
	private function refreshToken(){
		if (($this->get_database_setting('created_on') + $this->get_database_setting('expires_in')) <=  time() ){
			$this->setGapiValues(array( 'client_id'     => $this->get_database_setting('client_id'),
										'client_secret' => $this->get_database_setting('client_secret'),
										'refresh_token' => $this->get_database_setting('refresh_token')));
			$results = $this->gapi->refreshAccessToken();
			$this->set_database_setting(array('access_token'  => $results['access_token'],
											  'token_type'    => $results['token_type'],
											  'expires_in'    => $results['expires_in'],
											  'created_on'    => time()));
			$this->purgeCache();
		}
		$this->setGapiValues(array('access_token' => $this->get_database_setting('access_token')));
	}
	

	public function setProfileID($lrdata){
		$data = DataStore::get_from_cache($this->cachePrefix, md5('all_accounts_and_profiles')."_output", $this->cacheTime);
		$selectedProfile = array();
		
		if (!empty($data['all_accounts']) && is_array($data['all_accounts'])){
			foreach ($data['all_accounts'] as $account){
				if ($account['id'] == $lrdata['account_id']){
					$selectedProfile['account_id'] = $account['id'];
					foreach ($account['webProperties'] as $webProperty){
						if ($webProperty['id'] == $lrdata['property_id']){
							$selectedProfile['property_id'] = $webProperty['id'];
							foreach ($webProperty['profiles'] as $profile){
								if ($profile['id'] == $lrdata['profile_id']){
									$selectedProfile['profile_id'] = $profile['id'];
									break;
								}
							}
							break;
						}
					}
					break;	
				}
			}
		}
		
		if (!empty($data['all_profiles']) && is_array($data['all_profiles'])){
			foreach ($data['all_profiles'] as $profileTm){
				if ($profileTm['id'] == $lrdata['profile_id']){
					$selectedProfile['profile_timezone'] = $profileTm['timezone'];
					break;
				}
			}
		}

		
		if(empty($selectedProfile['account_id'])){$this->errors[] = "Invalid Account ID";}
		if(empty($selectedProfile['property_id'])){$this->errors[] = "Invalid Property ID";}
		if(empty($selectedProfile['profile_id'])){$this->errors[] = "Invalid Profile ID";}
		if(empty($selectedProfile['profile_timezone'])){$this->errors[] = "Invalid Profile Timezone";}
		if (empty($this->errors)){
			$this->set_database_setting(array('account_id'        => $selectedProfile['account_id'],
											  'property_id'       => $selectedProfile['property_id'],
											  'profile_id'        => $selectedProfile['profile_id'],
											  'profile_timezone'  => $selectedProfile['profile_timezone']));
								 
			if(!empty($lrdata['enable_universal_tracking']) && $lrdata['enable_universal_tracking'] === "on"){
				$this->set_database_setting(array('enable_universal_tracking'  => 'on'));
			}else{
				$this->set_database_setting(array('enable_universal_tracking'  => 'off'));
			}
			
			if(!empty($lrdata['enable_ecommerce_graph']) && $lrdata['enable_ecommerce_graph'] === "on"){
				$this->set_database_setting(array('enable_ecommerce_graph'  => 'on'));
			}else{
				$this->set_database_setting(array('enable_ecommerce_graph'  => 'off'));
			}			
			$this->purgeCache();
		}
		$this->jsonOutput();
	}

	public function settingsReset(){
		DataStore::reset_settings();
		$this->purgeCache();
		$this->output = array("setup" => 1);
		$this->jsonOutput();
	}	

	public function setDateRange($start_date, $end_date){
		if (($this->get_session_setting('start_date') != $start_date) || ($this->get_session_setting('end_date') != $end_date)){
			$this->set_session_setting(array('start_date' => $start_date, 'end_date' => $end_date));
			$this->purgeCache();
		}
	}

	public function setSystemTimeZone($systemTimeZone){
		$this->systemTimeZone = $systemTimeZone; 
	}	

    private function purgeCache(){
		if ($this->cache){
			DataStore::purge_cache($this->cachePrefix);
		}
	}	

	private function get_database_setting($name){
		return DataStore::database_get("settings",$name);
	}
	
	private function get_session_setting($name){
		return DataStore::session_get("settings",$name);
	}
	
	
	private function set_database_setting($settings){
		foreach ($settings as $name => $value){
			DataStore::database_set("settings",$name,$value);
		}
	}

	private function set_session_setting($settings){
		foreach ($settings as $name => $value){
			DataStore::session_set("settings",$name,$value);
		}
	}
	
	
	private function setGapiValues($kvPairs){
		foreach ($kvPairs as $key => $val){
			$this->gapi->$key = $val;
		}
	}

	private function calculateTotals(){
		if (isset($this->results['rows'])){
			$totalSessions = $this->results['totalsForAllResults'][$this->calculateTotalsFor];
			foreach ($this->results["rows"] as $index => $record){
				$this->results["rows"][$index][] = number_format(((end($record)*100)/$totalSessions),2);
			}
		$this->output = $this->results["rows"];
		}
	}

	private function preparePagesOutput(&$item){
		if (strpos($item[0], '/') === 0) {$item[0] = $item[2].$item[0];}		
		$item[0] = array(htmlspecialchars($item[0]),htmlspecialchars($item[1]));
		$item[1] = $item[3];
		unset($item[2]);
		unset($item[3]);
		$item = array_values($item);
	}

	private function array_find($needle, array $haystack){
		foreach ($haystack as $key => $value) {
			if (false !== stripos($value, $needle)) {
				return true;
			}
		}
		return false;
	}	
	
	private function convertDate(&$item){
		$item[0] = strtotime($item[0]." UTC") * 1000;
	}
	
	private function roundNumbers($num){
		$rounded =  floor($num * 100) / 100	;
        return $rounded; 		
	}	
	
	private function jsonOutput(){
		@ini_set('precision', 14);
		@ini_set('serialize_precision', 14);		
		if (empty($this->errors)){
			if ($this->cached){ $this->output['cached'] = "true";}
			if ($this->cachedOutput){ $this->output['cachedOutput'] = "true";}
			$this->output['system_timezone'] = $this->systemTimeZone;
			$this->output['gaview_timezone'] = $this->get_database_setting('profile_timezone');
			$this->output['start'] = $this->get_session_setting('start_date');			
			$this->output['end'] = $this->get_session_setting('end_date');
			$this->output['status'] = "done";
			OutputHandler::jsonOutput($this->output);
		}else{ ErrorHandler::FatalError(__('Fatal Error', 'lara-google-analytics'),__('Something went wrong .. please contact an administrator', 'lara-google-analytics'),100001,$this->errors);  }
		
		exit();
	}	
	
}
?>