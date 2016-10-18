<?php
$servername = "localhost";
$username = "root";
$password = "";

//echo $result->access_token;

// Create connection
$conn = mysqli_connect($servername, $username, $password,"adaframework");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//print_r($_POST['jsonData']);
if($_POST['action_type']=="insert_json")
{
	
	exit();
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "http://puppetnode.happiestminds.com:8080/plugin/global-build-stats/api/json?depth=2&buildStatConfigId=6S0h5JlNDGGTyaWNTD1HatnXX72IRotq");
$result = curl_exec($ch);
curl_close($ch);

$obj = json_decode($result,true);
	
	
	$array=$obj;//get_object_vars($obj);//$_POST['jsonData']; //json_decode($_POST['jsonData']);
	$i=0;
	$sql=""; $FLAG=0;
	$sql = "INSERT INTO build_status (build_id, build_status, build_value,build_start,build_end)
			VALUES ";
$FLAG=0;
//print_r($array['dimensions'][0]['columns']);
//if(sizeof($array['dimensions'][0]['columns'])>0){ echo "in"; exit();}
	foreach($array['dimensions'][0]['columns'] as $values)
	{       
	  $sql1="SELECT build_status,
		build_start,
		build_end
		FROM `build_status` 
		WHERE build_status='".$array['dimensions'][0]['rows'][$i]."'
		AND build_value='".$array['dimensions'][0]['values'][$i]."'
		AND build_start='".gmdate("Y-m-d H:i:s",$array['dimensions'][0]['columns'][$i]['start']/1000)."'
		AND build_end='".gmdate("Y-m-d H:i:s",$array['dimensions'][0]['columns'][$i]['end']/1000)."'
		ORDER BY `build_status`.`build_id` ASC limit 0,20";
		 $result = $conn->query($sql1);
		//print_r($result);
		if ($result->num_rows == 0) {
			//echo "in";
		$FLAG=1;
		$sql.="('',";
		$sql.="'".$array['dimensions'][0]['rows'][$i]."',";
		$sql.="'".$array['dimensions'][0]['values'][$i]."',";
		$sql.="'".gmdate("Y-m-d H:i:s",$array['dimensions'][0]['columns'][$i]['start']/1000)."',";
		$sql.="'".gmdate("Y-m-d H:i:s",$array['dimensions'][0]['columns'][$i]['end']/1000)."'),";
		}
		$i++;
	}

	 $sql=rtrim($sql, ",");
	if($FLAG==1)
{
	if((mysqli_query($conn,$sql))===TRUE){
		printf("Updated successfully..\n");
	}else{
		printf("Error: %s\n",mysqli_error($conn));
	}
}
	//echo $sql;

}elseif($_POST['action_type']=="plot_graph")
{

		/*$sql="SELECT build_status,
		SUM(build_value) AS build_value,
		DATE_FORMAT(build_start,'%H-%i-%S') AS build_start,
		DATE_FORMAT(build_end,'%H-%i-%S') AS build_end
		FROM `build_status` 
		group by date(`build_start`),hour(`build_start`),`build_status` 
		ORDER BY `build_status`.`build_id` ASC limit 0,20";*/
		$sql='SELECT SUM(IF(build_status="5) Success",build_value,0)) AS Success, 
		SUM(IF(build_status="2) Failures",build_value,0)) AS Failures, 
		SUM(IF(build_status="4) Unstables",build_value,0)) AS Unstables, 
		SUM(IF(build_status="3) Aborted",build_value,0)) AS Aborted, 
		SUM(IF(build_status="1) Not build",build_value,0)) AS Not_build, build_status, 
		SUM(build_value) AS build_value, 
		DATE_FORMAT(build_start,"%H-%i-%S") AS build_start, 
		DATE_FORMAT(build_end,"%H-%i-%S") AS build_end 
		FROM `build_status` group by date(`build_start`),hour(`build_start`) ORDER BY `build_status`.`build_id` ASC limit 0,20'; 
		$result = $conn->query($sql);

	$Success;
	$Failures;
	$Unstables;
	$Aborted;
	$Not_build;
	$i=0;
	if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
			$Success[$i]=0;
			$Failures[$i]=0;
			$Unstables[$i]=0;
			$Aborted[$i]=0;
			$Not_build[$i]=0;
	    $date_time[$i]='"'.$row['build_start'].' to '.$row['build_start'].'"';
		if($row['Success']>0 && $row['Success']!="")
		{
			$Success[$i]=$row['Success'];
			
		}if($row['Failures']>0 && $row['Failures']!=""){
			$Failures[$i]=$row['Failures'];
			
		}if($row['Unstables']>0 && $row['Unstables']!=""){
			$Unstables[$i]=$row['Unstables'];
			
		}if($row['Aborted']>0 && $row['Aborted']!=""){
			$Aborted[$i]=$row['Aborted'];
			
		}if($row['Not_build']>0 && $row['Not_build']!=""){
			$Not_build[$i]=$row['Not_build'];
			
		}
	$i++;
	}
	} else {
	echo "0 results";
	}
$date_time_unique=array_unique($date_time);
$date_time_unique=implode(",",$date_time);
$Success=implode(",",$Success);
$Failures=implode(",",$Failures);
$Unstables=implode(",",$Unstables);
$Aborted=implode(",",$Aborted);
$Not_build=implode(",",$Not_build);


echo '
<script>
StandaloneDashboard(function(db){

	db.setDashboardTitle ("Engineering Health");


	var chart = new ChartComponent("Jenkins Build Status");
	chart.setDimensions (8, 6);
	chart.setCaption("Jenkins Build Status");
	chart.setLabels (['.$date_time_unique.']);
	chart.addSeries ("Success", "Success", ['.$Success.'], {seriesDisplayType: "line"});
	chart.addSeries ("Aborted", "Aborted", ['.$Aborted.'], {seriesDisplayType: "line"});
	chart.addSeries ("Unstables", "Unstables", ['.$Unstables.'], {seriesDisplayType: "line"});
	chart.addSeries ("Failures", "Failures", ['.$Failures.'], {seriesDisplayType: "line"});
	chart.addSeries ("Not_build", "Not build", ['.$Not_build.'], {seriesDisplayType: "line"});
	chart.setYAxis("Build count", {numberPrefix: " ", numberHumanize: true});
	db.addComponent (chart);
/*
	var chart2 = new ChartComponent();
	chart2.setCaption("Sales");
	chart2.setDimensions (6, 6);	
	chart2.setLabels (["2013", "2014", "2015"]);
	chart2.addSeries ([3151, 1121, 4982], {
		numberPrefix: "$",
		seriesDisplayType: "line"
	});
	db.addComponent (chart2);
*/
});</script>';

}
?>