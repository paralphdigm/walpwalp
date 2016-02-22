<?php
	//include("security.php");
	include("db.php");
	//$security = new security();
				
class vehicleresourcesmanager{
		// Smart GET function
		public function GET($name=NULL, $value=false, $option="default")
		{
			$option=false; // Old version depricated part
			$content=(!empty($_GET[$name]) ? trim($_GET[$name]) : (!empty($value) && !is_array($value) ? trim($value) : false));
			if(is_numeric($content))
				return preg_replace("@([^0-9])@Ui", "", $content);
			else if(is_bool($content))
				return ($content?true:false);
			else if(is_float($content))
				return preg_replace("@([^0-9\,\.\+\-])@Ui", "", $content);
			else if(is_string($content))
			{
				if(filter_var ($content, FILTER_VALIDATE_URL))
					return $content;
				else if(filter_var ($content, FILTER_VALIDATE_EMAIL))
					return $content;
				else if(filter_var ($content, FILTER_VALIDATE_IP))
					return $content;
				else if(filter_var ($content, FILTER_VALIDATE_FLOAT))
					return $content;
				else
					return preg_replace("@([^a-zA-Z0-9\+\-\_\*\@\$\!\;\.\?\#\:\=\%\/\ ]+)@Ui", "", $content);
			}
			else false;
		}

		function getallvehicleinfo($table) {
			$sql = "SELECT id,vehicle_platenumber,vehicle_description,vehicle_owner FROM `$table` ORDER BY id";
			try {
				$db = getDB();
				$stmt = $db->query($sql);  
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);
				$db = null;
				echo '{"vehicle":'.json_encode($users). '}';
			} catch(PDOException $e) {
				//error_log($e->getMessage(), 3, '/var/tmp/php.log');
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		
		function getallvehicleinfo_by_id($key) {
			$sql = "SELECT id,vehicle_platenumber,vehicle_description,vehicle_owner FROM vehicle_info WHERE vehicle_platenumber = `$key`";
			try {
				$db = getDB();
				$stmt = $db->query($sql);  
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);
				$db = null;
				echo '{"vehicle":' . json_encode($users) . '}';
			} catch(PDOException $e) {
				//error_log($e->getMessage(), 3, '/var/tmp/php.log');
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		function getallVehicleLogs() {
			$sql = "SELECT A.id, A.vehicle_platenumber, A.vehicle_description, A.vehicle_owner, B.id, B.vehicle_latitude, B.vehicle_longitude,B.vehicle_location,B.ip_address,B.vehicle_code_fk, B.date_created FROM vehicle_info A, vehicle_logs B WHERE A.id=B.vehicle_code_fk  ORDER BY B.id ASC";
			try {
				$db = getDB();
				$stmt = $db->prepare($sql); 
				$stmt->execute();		
				$updates = $stmt->fetchAll(PDO::FETCH_OBJ);
				$db = null;
				echo '{"vehicle": '.json_encode($updates). '}';
				
			} catch(PDOException $e) {
				//error_log($e->getMessage(), 3, '/var/tmp/php.log');
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}

		function getallVehicleLogs_by_id($vehicle_log_id) {
			$sql = "SELECT A.vehicle_code, A.vehicle_platenumber, A.vehicle_description, A.vehicle_owner, B.vehicle_log_id, B.vehicle_latitude, B.vehicle_longitude,B.vehicle_location,B.ip_address,B.vehicle_code_fk, B.date_created FROM vehicle_info A, vehicle_logs B WHERE A.vehicle_code=B.vehicle_code_fk  AND B.vehicle_log_id=:vehicle_log_id";
			try {
				$db = getDB();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("vehicle_log_id", $vehicle_log_id);		
				$stmt->execute();		
				$updates = $stmt->fetchAll(PDO::FETCH_OBJ);
				$db = null;
				echo '{"vehicle":' . json_encode($updates) . '}';
				
			} catch(PDOException $e) {
				//error_log($e->getMessage(), 3, '/var/tmp/php.log');
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
		//add functions
		function addvehiclelog($vehicle_latitude,$vehicle_longitude,$vehicle_location,$ip_address,$vehicle_code) {
			$sql = "INSERT INTO vehicle_logs (vehicle_latitude,vehicle_longitude,vehicle_location,ip_address, vehicle_code_fk, date_created) VALUES (:vehicle_latitude,:vehicle_longitude,vehicle_location,:ip_address, :vehicle_code, :date_created)";
			//try {
				$db = getDB();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("vehicle_latitude", $vehicle_latitude);
				$stmt->bindParam("vehicle_longitude", $vehicle_longitude);
				$stmt->bindParam("ip_address", $ip_address);
				$stmt->bindParam("vehicle_code", $vehicle_code);
				date_default_timezone_set("US/Pacific");
				$date_created = date("Y-m-d h:i:sa") ;
				$stmt->bindParam("date_created", $date_created);
				$stmt->execute();
				$user_id = $db->lastInsertId();
				$db = null;
				echo true;
			//} catch(PDOException $e) {
			//	//error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			//}
		}
		function addvehicle($vehicle_platenumber,$vehicle_description,$vehicle_owner) {
			  $sql = "INSERT INTO vehicle_info (vehicle_platenumber,vehicle_description,vehicle_owner) VALUES (:vehicle_platenumber,:vehicle_description,:vehicle_owner)";
			 // try {
				$db = getDB();
				$stmt = $db->prepare($sql);
				  $stmt->bindParam("vehicle_platenumber", $vehicle_platenumber);
				  $stmt->bindParam("vehicle_description", $vehicle_description);
				  $stmt->bindParam("vehicle_owner", $vehicle_owner);
				  $stmt->execute();
				  $db = null;
				  echo true;
			 // } catch(PDOException $e) {
				//  echo json_encode($e->getMessage());
			 // }
			}

		//delete functions
		function deletevehicleinfo($table,$key) {
		   
			$sql = "delete from`$table` where id=$key";
			try {
				$db = getDB();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("id", $key);
				$stmt->execute();
				$db = null;
				echo true;
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
			
		}
		function gettable_by_id($table,$key,$link){
			
			$sql = "select * from `$table` WHERE id = $key";
			$result = mysqli_query($link,$sql);	
			for ($i=0;$i<mysqli_num_rows($result);$i++) {
				echo ($i>0?',':'').'{"vehicle":[' .json_encode(mysqli_fetch_object($result)). ']}';
			 }
		}

		function updatevehicleinfo($table,$key,$vehicle_platenumber,$vehicle_description,$vehicle_owner)  {
			$sql = "update `$table` set vehicle_platenumber=:vehicle_platenumber, vehicle_description=:vehicle_description, vehicle_owner=:vehicle_owner where id =$key";
			try {
				$db = getDB();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("vehicle_platenumber", $vehicle_platenumber);
				$stmt->bindParam("vehicle_description", $vehicle_description);
				$stmt->bindParam("vehicle_owner", $vehicle_owner);
				$stmt->bindParam("id", $key);
				$stmt->execute();
				$db = null;
				echo true;
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}';
			}
		}
		function filtervehicleinfo($table,$filter) {
			for($i = 0; $i < count($filter); $i++){
				$temp[] = implode(" ",$filter[$i]);
			}
			$filters =implode(',',$temp);
			
			$sql = "SELECT id,vehicle_platenumber,vehicle_description,vehicle_owner FROM `$table` WHERE ".$filters;
		//	try {
				$db = getDB();
				$stmt = $db->query($sql);  
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);
				$db = null;
				if(empty($users))
					$this -> checker();
				else
					echo '{"vehicle":' . json_encode($users) . '}';
			//} catch(PDOException $e) {
			//	//error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			//}

		}

		
		/*
		
		function getUserSearch($query) {
			$sql = "SELECT user_id,username,name,profile_pic FROM users WHERE UPPER(name) LIKE :query ORDER BY user_id";
			try {
				$db = getDB();
				$stmt = $db->prepare($sql);
				$query = "%".$query."%";  
				$stmt->bindParam("query", $query);
				$stmt->execute();
				$users = $stmt->fetchAll(PDO::FETCH_OBJ);
				$db = null;
				echo '{"users": ' . json_encode($users) . '}';
			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}'; 
			}
		}
			*/
	
}
class REST extends vehicleresourcesmanager{
		protected function likeEscape($string) {
			return addcslashes($string,'%_');
		}
		public function error403($value) {
			if (isset($_SERVER['REQUEST_METHOD'])) {
				header('Content-Type:',true,403);
				die('Forbidden');
			} else {
				throw new \Exception(json_encode($value) );
			}
		}
		public function error409($value)  {
			if (isset($_SERVER['REQUEST_METHOD'])) {
				header('Content-Type:',true,409);
				die('Conflict');
			} else {
				throw new \Exception(json_encode($value) );
			}
		}
		public function error422($value)  {
			if (isset($_SERVER['REQUEST_METHOD'])) {
				header('Content-Type:',true,422);
				die(json_encode($value));
			} else {
				throw new \Exception(json_encode($value) );
			}
		}
		public function errorCorsHeaders() {
			$headers = array();
			$headers[]='Access-Control-Allow-Headers: Content-Type';
			$headers[]='Access-Control-Allow-Methods: OPTIONS, GET, PUT, POST, DELETE';
			$headers[]='Access-Control-Max-Age: 1728000';
			if (isset($_SERVER['REQUEST_METHOD'])) {
				foreach ($headers as $header) header($header);
				die();
			} else {
				throw new \Exception(json_encode($headers));
			}
		}		
		protected $settings;
		protected function mapMethodToAction($method,$key) {
			switch ($method) {
				case 'OPTIONS': $this->errorCorsHeaders();
				case 'GET': return 'read';
				case 'PUT': return 'update';
				case 'POST': return 'create';
				case 'DELETE': return 'delete';
				default: ;
			}
		}
			protected function parseReqParam(&$request,$characters,$default) {
				if (!count($request)) return $default;
				$value = array_shift($request);
				return $characters?preg_replace("/[^$characters]/",'',$value):$value;
			}
			protected function parseGetParam($get,$name,$characters,$default) {
				$value = isset($get[$name])?$get[$name]:$default;
				return $characters?preg_replace("/[^$characters]/",'',$value):$value;
			}
			protected function parseGetParamArray($get,$name,$characters,$default) {
				$values = isset($get[$name])?$get[$name]:$default;
				if (!is_array($values)) $values = array($values);
				if ($characters) {
					foreach ($values as &$value) {
						$value = preg_replace("/[^$characters]/",'',$value);
					}
				}
				return $values;
			}
		protected function processFilterParameter($filter) {
			$value = '';
			if ($filter) {
				$filter = explode(',',$filter,3);
				if (count($filter)==3) {
					$match = $filter[1];
					$filter[1] = 'LIKE';
					if ($match=='cs') $filter[2] = "'".'%'.$this->likeEscape($filter[2])."%'";
					else if ($match=='sw') $filter[2] = "'".$this->likeEscape($filter[2])."%'";
					else if ($match=='ew') $filter[2] = '%'.$this->likeEscape($filter[2]);
					if ($match=='eq') $filter[1] = '=';
					if ($match=='ne') $filter[1] = '<>';
					if ($match=='lt') $filter[1] = '<';
					if ($match=='le') $filter[1] = '<=';
					if ($match=='ge') $filter[1] = '>=';
					if ($match=='gt') $filter[1] = '>';
					if ($match=='in') {
						$filter[1] = 'IN';
						$filter[2] = explode(',',$filter[2]);
					}
				} else {
					return false;
				}
			}
			return $filter;
		}
		protected function getParameters($settings) {
			extract($settings);
			$apikey    = $this->parseReqParam($request, 'a-zA-Z0-9\-_*,', false);
			$table     = $this->parseReqParam($request, 'a-zA-Z0-9\-_*,', false);
			$key       = $this->parseReqParam($request, 'a-zA-Z0-9\-,', false); // auto-increment or uuid
			$action    = $this->mapMethodToAction($method,$key);
			$filters   = $this->parseGetParamArray($get, 'filter', false, false);
			$order     = $this->parseGetParam($get, 'order', 'a-zA-Z0-9\-_*,', false);
			foreach ($filters as &$filter) $filter = $this->processFilterParameter($filter);
			//if (empty($table)) $this->error403($value);
			// reflection

			return compact('action','apikey','table','key','filters','order');
		}
	public function filtercolumnchecker($table,$filter,$db){
		
				$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :table";
				$db = getDB();
				$stmt = $db->prepare($sql);
				$stmt->bindparam("table", $table);
				$stmt->execute();
				$output = array();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
						$output[]= $row['COLUMN_NAME'];     
						
					}
					$filters = explode(" ",$filter[0][0]);
					$result=array_intersect($output,$filters);
					$result = implode(" ",$result);
				if(!$result){

						return false;	
				}
				else{

						return true;
				}
					

	}
	public function filterinputchecker($table,$filter,$db){
		$filtercolumnchecker = $this->filtercolumnchecker($table,$filter,$db);
		if($filtercolumnchecker == true){
			$query = mysqli_query($db,"select * from $table WHERE ".$filter[0][0]."='".$filter[0][2]."'");
			$numrows = mysqli_num_rows($query);
			$temp = $filter[0][0];
			$v = $filter[0][2];
				if($numrows != 0){
					
					while($row = mysqli_fetch_assoc($query)){
						$k = $row[$temp];
					}
					if($k == $v)
						return true;
					else
						return false;
				}
				else
					return false;
		}
		else
			return false;
		
		
	}
	public function apikeychecker($db,$apikey){
		
		$query = mysqli_query($db,"SELECT * FROM tbl_user WHERE apikey ='".$apikey."'");
		$numrows = mysqli_num_rows($query);

			if($numrows != 0){
								
				while($row = mysqli_fetch_assoc($query)){
					$akey = $row['apikey'];
				}
				if($apikey == $akey)
					return true;
				else
					return false;
			}

	}
	public function __construct() {
		//extract($config);
		$charset = isset($charset)?$charset:null;
		$method = isset($method)?$method:null;
		$request = isset($request)?$request:null;
		$get = isset($get)?$get:null;
		$post = isset($post)?$post:null;
		// defaults
		if (!$method) {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		if (!$request) {
			$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
		}
		if (!$get) {
			$get = $_GET;
		}
		if (!$post) {
			$post = 'php://input';
		}
		//if (!$charset) {
		//	$charset = $this->getDefaultCharset();
		//}

		$this->settings = compact('method', 'request', 'get', 'post');
	}
	
	public function executeCommand() {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			header('Access-Control-Allow-Origin: *');
		}
		
		$value = ''; 
		$link = mysqli_connect('localhost', 'paralphdigm', 'xnd5721ha7yg', 'vtms_db');
		mysqli_set_charset($link,'utf8');
		
		
		$parameters = $this->getParameters($this->settings);
		$filtercolumnchecker = $this->filtercolumnchecker($parameters['table'],$parameters['filters'],$link);
		$filterinputchecker = $this->filterinputchecker($parameters['table'],$parameters['filters'],$link);
		$apikeychecker = $this->apikeychecker($link,$parameters['apikey']);
		
					if($apikeychecker == true){
						
						switch ($parameters['action'] ) {
							
							  case 'read':
							  if($parameters['key']){
									$value =$this->gettable_by_id($parameters['table'],$parameters['key'],$link);
									return $parameters['filters'];
							  }							  		  
							  else{
									if($parameters['table'] == 'vehicle_info'){
										$filters = json_encode($parameters['filters']);
											if($parameters['filters'] == [false])
												  $value = $this->getallvehicleinfo($parameters['table']);	
											else
												  if($filtercolumnchecker == true && $filterinputchecker == true)

															$value = $this->filtervehicleinfo($parameters['table'],$parameters['filters']);
												  else
													$this->error403($value);
									}
									else if($parameters['table'] == 'vehicle_logs')
										$value =$this->getallvehiclelogs();
									else
										$this->error403($value);
							  }
							break;

							  case 'update':
									 if($parameters['key']){ 
											if($parameters['table'] == 'vehicle_info'){
												if(isset($_POST["vehicle_platenumber"]) && isset($_POST["vehicle_platenumber"]) && isset($_POST["vehicle_owner"])  ){
													$value = $this->updatevehicleinfo($parameters['table'],$parameters['key'],$_POST["vehicle_platenumber"],$_POST["vehicle_description"],$_POST["vehicle_owner"]);
													
												}
										 
											}						  
											  else if($parameters['table'] == 'vehicle_logs')
												$this->error403($value);
									else
												$this->error403($value);
											
											
									}
									break;
							  case 'create':					  
									  if($parameters['key'])						  
										  $this->error403($value);
									  else{
										  if($parameters['table'] == 'vehicle_info'){
											if(isset($_POST["vehicle_platenumber"]) && isset($_POST["vehicle_platenumber"]) && isset($_POST["vehicle_owner"]) ){
												$value = $this->addvehicle($_POST["vehicle_platenumber"],$_POST["vehicle_description"],$_POST["vehicle_owner"]);
												
											}else
												$this->error403($value);
										}
										else if($parameters['table'] == 'vehicle_logs'){
											$value = $this->addvehiclelog($_POST["vehicle_latitude"],$_POST["vehicle_longitude"],$_POST["vehicle_location"],$_POST["ip_address"],$_POST["vehicle_code"]);
										}
										else
										$this->error403($value);
									  }
									break;
							  case 'delete':
								$value = $this->deletevehicleinfo($parameters['table'],$parameters['key']);
								break;
						}
						//	return(json_encode($value));
						
					}		
					else
						$this->error403($value);
			
				}
	
		
}

$api = new REST();
$api->executeCommand();


?>