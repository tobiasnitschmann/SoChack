<?php
 $mysqli = new mysqli("<DB HOST>", "<DB USER>", "<DB PASSWORD>", "<DB NAME>");
 $stmt = $mysqli->prepare("SELECT * FROM bms ORDER BY timestamp DESC LIMIT 30");
 
 if(isset($_REQUEST['update'])) {
	
	
	$res = array();
/* Create table doesn't return a resultset */

$state = "";

	
	
	
	$stmt->execute();
    if ($result = $stmt->get_result()) {
		while( $row = $result->fetch_array(MYSQLI_ASSOC)){
  			$res[] = $row;
  		}
  		$res[0]['state'] = getState($res);
  		echo json_encode($res[0]);
  		//echo json_encode(array('soc' => rand(0,100), 'timestamp'  => '2010-11-11 06:01:00'));
  		exit;
  	}
 }
 ?>

<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <link rel="apple-touch-icon" href="SoCheck-icon.png"/>
    
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>SoChack</title>


	<script src="http://code.jquery.com/jquery-1.12.4.min.js"></script>


	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<!--<link rel="stylesheet" href="css/jquery.circliful.css" type="text/css" >-->
	<link rel="stylesheet" href="css/circliful.css" type="text/css">
	<script src="js/circliful.min.js"></script>
	<script type="text/javascript">
    	$(document).ready(function() {
			$("#ioniq").circliful({
                'animation-step': 2,
                'time-between-frames' : 20,
                'foreground-width' : 9,
                'background-width': 15,
                'background-fill' : false,
                'dimension' : 350
            });
            
 
   			var update = setInterval(function() {
   				$.ajax({
   					url: '?update',
   					method: 'GET',
   					dataType: 'json',
   					cache: false,
   					success : function(data) {
   						$("#ioniq").circliful('animateToPercentage', data['soc']);
   						// Split timestamp into [ Y, M, D, h, m, s ]
						var t = data['timestamp'].split(/[- :]/);
						
						// Apply each element to the Date function
						var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
   						var date = d.getUTCDate() + "." + (d.getUTCMonth() + 1) + "." + d.getFullYear()  + " " + ("0" + (d.getUTCHours() )).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2)  + " Uhr";
   						$("p strong").html(date);
   						$("p #status").html(data['state']);
   					}
   				
   				});
   			}, 10000)
   		});
   		
	</script>
	<style type="text/css">
		html {
			font-family:Arial
		}
		.circle-text::after {
			content: " %";
		}
		p {
			font-size:17px;
		}
		.fix{
    		position:fixed;
    		bottom:0px;
    		left: 50%;
    		transform: translate(-50%, 0);
    	}
    	
    	@media (max-width: 700px) and (orientation:landscape) {
    		img {
    		    display:none;
    		}
    		#visualState {
    			float:left;
    			
    		}
    		
			#factSheet {
    			margin-top:8em;
    			
    		}
		}


	</style>
</head>
<body>
	<div class="container">
<?php


/* check connection */
if ($mysqli->connect_error) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$res = array();
/* Create table doesn't return a resultset */
$stmt->execute();
if ($result = $stmt->get_result()) {

	while( $row = $result->fetch_array(MYSQLI_ASSOC)){
	    $res[] = $row;
	}
}

function getState($array) {
	//print_r($array);
	$compare = array($array[0]);
	$current = $array[0]['soc'];
	foreach($array as $status) {
		if($status['soc'] !== $current) {
			$compare[] = $status;
			$current = $status['soc'];
		}
	}
	$last_date = new DateTime($compare[0]['timestamp']);
	$start_date = new DateTime($compare[1]['timestamp']);
	$since_lastSoCupdate = $start_date->diff(new DateTime($compare[2]['timestamp']));
	$since_start = $last_date->diff(new DateTime());
	
	if($since_start->i < 10) {
		if($compare[0]['soc'] > $compare[1]['soc']) {
			// Fahrzeug läd auf
			//echo $since_lastSoCupdate->i;
			
			if($since_lastSoCupdate->i >= 6 && $since_lastSoCupdate->i <= 12) {
				$speed = "langsam";
				$minutes = (100 - $compare[0]['soc']) * 7.2;
				
			} else if($since_lastSoCupdate->i >= 4 && $since_lastSoCupdate->i <= 6) {
				$speed = "mittel";
				$minutes = (100 - $compare[0]['soc']) * 5;
				
			} else if ($since_lastSoCupdate->i > 1 && $since_lastSoCupdate->i <= 3) {
				$speed = "schnell";
				$minutes = (100 - $compare[0]['soc']) * 2;
				
			}
			$state = "<b>lädt auf</b><br />" . 
					 "Geschwindigkeit: <b>" . $speed . "</b><br />" . 
					 "verbleibende Ladezeit: <b>" .($minutes >= 60 ? date('G', mktime(0,$minutes)) . "h " : "") . date('i', mktime(0,$minutes)) .  " min</b>";
		} else if ($array[0]['soc'] < $array[1]['soc']) {
			// Fahrzeug fährt
			$state = "<b>fährt</b>";
		}
	} else {
		$state = "<b>parkt</b>";
	}
	return $state;
}


?>
<div class="row" id="visualState">
    <div class="" style="text-align:center;height:300px">
    	<div style="width:350px;margin:auto;">
        	<div id="ioniq" data-percent="<?php echo $res[0]['soc']; ?>"></div>
        </div>
    </div>
</div>
<div id="factSheet">
		<p style="text-align:center">Fahrzeug: <span id="status"><?php echo getState($res);?></span></p><br />
	   <p style="text-align:center">Letzte Aktualisierung: <br />
	   	<strong><?php echo date('d.m.Y H:i', strtotime($res[0]['timestamp'])); ?> Uhr</strong>
	   </p>
</div>
	   <?php
	   	


$mysqli->close();
?>

<img src="ioniq.jpg" class="fix hidden-lg" width="100%" />
<div style="text-align:center">
	<img src="ioniq.jpg" class="fix visible-lg" width="40%" />
</div>

</div>
</body>