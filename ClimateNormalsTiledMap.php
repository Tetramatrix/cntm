<?php
//error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
ini_set("max_execution_time","1000");
set_time_limit(1000);

define("ALPHA",12.5);
define("STEPS",6);
require_once("contour.php");

function LabelString($parameter,$Z,$mean) {		 
	if ($parameter=="Mean")
		$s=	"Mean average temperature = ".round($mean,1).", max warmer difference = ".round(max($Z)-$mean,1).", max cooler difference = ".-round($mean-min($Z),1);
	else if ($parameter=="Maximum")
		$s=	"Mean maximum temperature = ".round($mean,1).", max warmer difference = ".round(max($Z)-$mean,1).", max cooler difference = ".-round($mean-min($Z),1);
	else if ($parameter=="Minimum")
		$s=	"Mean minimum temperature = ".round($mean,1).", max warmer difference = ".round(max($Z)-$mean,1).", max cooler difference = ".-round($mean-min($Z),1);
	else if ($parameter=="HDD")
		$s="Mean HDD = ".round($mean,1).", Max HDD ".round(max($Z),1).", Min HDD ".round(min($Z),1);
	else if ($parameter=="CDD")
		$s="Mean CDD = ".round($mean,1).", Max CDD ".round(max($Z),1).", MIN CDD ".round(min($Z),1);  
	else if (($parameter=="Precip") || ($parameter=="Snow")) 
		$s="Mean precip/snow = ".round($mean,1).", Max precip/snow ".round(max($Z),1).", MIN precip/snow ".round(min($Z),1);
	else {};
	return $s;
}	
function GraphSiteData($im,$D,$x0,$y0,$color) {  				   
	$r=6;
	for ($i=0; $i<sizeof($D); $i+=3) {	 
		ImageFilledEllipse($im,$x0+$D[$i],$y0+$D[$i+1],$r,$r,$color);
	}
}
function DrawStateBoundaries($im,$points,$part,$color,$x0,$y0)	{
	$x1=$points[0]; $y1=$points[1];	$k=0;
	for ($i=3; $i<sizeof($points); $i+=2) {	
		$k++; 
		if ($part[$k] == $part[$k-1]) ImageLine($im,$x0+$x1,$y0+$y1,$x0+$points[$i-1],$y0+$points[$i],$color);
		$x1=$points[$i-1]; $y1=$points[$i];
	}	
}	
function StateBoundaries($State,$Lon_pix,$Lat_pix) {		
 	$FileIn="StateMaps.txt";
	if (file_exists($FileIn)) $in_map=fopen($FileIn,"r");
	else exit("File doesn't exist."); 	   
	$points=array();	$A=array();
	// Read Lon/Lat boundaries and get max and min values. 		
	$maxLat=0; $minLat=90; $maxLon=-180; $minLon=-60; 	 
	do { $line=fgets($in_map); } while (substr($line,0,2) != $State);
	$k= -1;
	do {
		$k++; sscanf($line,"%s %f %f %f %f %f",$s,$x,$part[$k],$x,$Lon[$k],$Lat[$k]);	 
		if (strlen($line)>3) {
			sscanf($line,"%f %f",$Lon[$k],$Lat[$k]);		
			if ($Lat[$k]>$maxLat) $maxLat=$Lat[$k]; if ($Lat[$k]<$minLat) $minLat=$Lat[$k];	
			if ($Lon[$k]>$maxLon) $maxLon=$Lon[$k];	if ($Lon[$k]<$minLon) $minLon=$Lon[$k];		
		}
		$line=fgets($in_map);  //echo $line;
	}	while (substr($line,0,2) == $State)	;	 
	fclose($in_map);					  
	$Lat_max=$maxLat; $Lat_min=$minLat;	$Lon_max=$maxLon; $Lon_min=$minLon;
	$Lon_range=$Lon_max-$Lon_min; $Lat_range=$Lat_max-$Lat_min;	$x_scale=.7; $y_scale=1;
	if ($Lon_range>$Lat_range) $y_scale=$Lat_range/$Lon_range;
	if ($Lat_range>$Lon_range) $x_scale=.7*$Lon_range/$Lat_range;
    //$Lat_pix=round($Lat_range/$Lon_range)*$Lon_pix;	   
	// Make graphing coordinates file
	$p=-1; //$x0=50; $y0=0; 
	for ($i=0; $i<sizeof($Lat); $i++) {
		$p++; $points[$p]=round($x_scale*($Lon[$i]-$Lon_min)/$Lon_range*$Lon_pix); 
		$p++; $points[$p]=round($y_scale*($Lat_max-$Lat[$i])/$Lat_range*$Lat_pix);		  
	}	
	$A[0]=$points; $A[1]=$part; $A[2]=$Lon_min; $A[3]=$Lon_range; $A[4]=$Lat_max; $A[5]=$Lat_range;
	return $A;	
}  // end StateBoundaries  
// ------------- MAIN PROGRAM ----------------
$A=array();	$D=array(); $B=array(); $Mean=array(); $Mean_knt=0;		$elev=array();	  $lon_all=array(); $lat_all=array();
$CitySites=array();	  $data=array();
$M=array("","January","February","March","April","May","June","July","August","September","October",
	"November","December","Annual") ;	
// Define graphing space
$x_max=930; $y_max=700; // Maximum width and height of the graph space	
$x_size=600; $y_size=600; // Size of mapping space 
$x0=50; $y0_text=15; $y0_map=100; // offsets for drawing text and map within mapping space	 
// Get variables from HTML file
$parameter=$_POST["parameter"]; // equals Mean, Minimum, Maximum, etc...  	   
//$OutputType=$_POST["OutputType"]; 
$month=$_POST["month"]; 
if (isset($_POST["State"])) $State=$_POST["State"];
else exit("You must select a state.");	 	
$period=$_POST["period"];	
if ($period=="1971_2000")
	$Label="1971-2000 Climate Normals for ".$State; 
else 
	$Label="1981-2010 Climate Normals for ".$State;; 			
// Select proper data set
if ($period=="1971_2000") {	
  	$FileIn="ClimateNormals20002010/ClimateMeans".$State.".txt";	  
	//echo $FileIn;exit;
	if (file_exists($FileIn)) $in=fopen($FileIn,"r");
	else exit("File doesn't exist.");  	
}
else {
  	$FileIn="ClimateNormals20002010/ClimateMeans".$State."2010.txt";
	if (file_exists($FileIn)) $in=fopen($FileIn,"r");
	else exit("File doesn't exist.");  	
}		
// Read site data.
$K=-1; $mean=0;	 $n_sites=-1;	
// Read three file header lines (the 3rd line is blank).
$line=fgets($in); $line=fgets($in); $line=fgets($in); 
while (!feof($in)) {  
	// Read site headers (different for 1971-2000 and 1981-2010 data).	
	$city=fgets($in); $city=substr($city,strpos($city,":")+1); $state=fgets($in);	
	if ((strlen($city)<2) || (feof($in))) goto end_file; // Bail out at end of file, or if there are blank lines rather than more data.		
	$n_sites++;		
	if ($period=="1971_2000") {		 
		$lat=fgets($in); $lon=fgets($in); 
		$elev[$n_sites]=preg_replace("/[^0-9.]/","",fgets($in));   
		$ID=fgets($in);	  
		$lon=preg_replace("/[^0-9.]/","",$lon);
		$lat=preg_replace("/[^0-9.]/","",$lat);	 
		$lat=floor($lat)+($lat-floor($lat))*100/60;
		$lon=-(floor($lon)+($lon-floor($lon))*100/60); // This works as long as all longitudes are west longitudes.
	}
	else {	 	
		$ID=fgets($in); $lat=fgets($in); $lon=fgets($in); $county=fgets($in);
		$elev[$n_sites]=preg_replace("/[^0-9.]/","",fgets($in)); 	 
		$lon=substr($lon,strpos($lon,":")+1); $lat=substr($lat,strpos($lat,":")+1);  	
		$lon=ltrim($lon); $lat=ltrim($lat);	$signLon=""; $signLat="";	
		// The 1981-2010 files use negative values as appropriate (e.g., - for west longitude, 	
		// instead of N, S, E, W suffixes. Also decimal degrees rather than ddd.mm.
		// So "fixing" the Lon/Lat representations isn't necessary.
	}	
	$lon_all[$n_sites]=$lon; $lat_all[$n_sites]=$lat;								   
	// Read monthly data.	
	$line=fgets($in); // column headers	 
	$A[0]=$line; // $A[0] contains the column headers
	for ($i=1; $i<=7; $i++) { // from 1 to 7 rows of data
		$line=fgets($in); if (strlen($line)<5) break; // For less than 7 data rows, this will read the blank line.  
		$search="<"; $replace="#";
		for ($ch=0; $ch<strlen($line); $ch++) {		
			if ($line[$ch]=="<") $line[$ch]=" ";  // Remove "<" sign. Deal with the 0.5 value later.
		} 
		$A[$i]=ltrim($line);  
		if (substr($A[$i],0,3) == substr($parameter,0,3)) {	 
			sscanf($A[$i],"%s %f %f %f %f %f %f %f %f %f %f %f %f %f",$B[0],$B[1],$B[2],$B[3],$B[4],
				$B[5],$B[6],$B[7],$B[8],$B[9],$B[10],$B[11],$B[12],$B[13]);	
			$K++; $LonSites[$K]=$lon; $LatSites[$K]=$lat;
			$Z[$K]=$B[$month];	$mean+=$Z[$K]; $CitySites[$K]=$city;	
			for ($j=0; $j<=12; $j++) {
				$data[$K][$j]=$B[$j+1];
			}
		} 
	}
	// If the loop terminated without a break, then there is 
	// a blank line that hasn't been read yet. For normal termination, i=8 when the loop is done.
	if ($i==8) { 
		$line=fgets($in);				
	}  		
}	 
end_file: // This handles what happens at the end of the file and there are (empty?) lines but no more cities.
fclose($in);	
$mean/=($K+1);		
// Get shape file for selected state.															  
// A[0]= (lon,lat,lon,lat,...)  A[1]= sub-area value   A[2]= min Lon  A[3]= Lon range  A[4]= max Lat  A[5]= Lat range
$A=StateBoundaries($State,$x_size,$y_size);	  
// $D=(x,y,z,x,y,z,...)
$D=SiteData($K,$LonSites,$LatSites,$Z,$A[2],$A[3],$A[4],$A[5],$x_size,$y_size);
// This is just one possible definition of the statewide "mean" value for a parameter. 
$mean=(max($Z)+min($Z))/2;	
 
Header("Content-type: image/gif"); 
$im = imageCreate($x_max, $y_max) or die ("Cannot Initialize new GD image stream");
$background_color = ImageColorAllocate($im, 255, 255, 255);   	
$black=ImageColorAllocate($im,0,0,0); $red=ImageColorAllocate($im,255,0,0);	 
$blue=ImageColorAllocate($im,0,0,255);	  
ImageSetThickness($im,2);				
ImageString($im,5,$x0+10, $y0_text,$Label,$black); 
$RangeString=LabelString($parameter,$Z,$mean); 
ImageString($im,5,$x0+10,$y0_text+15,$RangeString,$black);

$plot=new Contourplot();
$res=$plot->main($D,$x_size,$y_size,$A,$mean,ALPHA);
$pic=new Image($plot);
$pic->draw($im);

DrawStateBoundaries($im,$A[0],$A[1],$black,$x0,$y0_map);

// Locates all sites with dots.	   
GraphSiteData($im,$D,$x0,$y0_map,$blue); 
//GraphSiteData($im,$K,$LonSites,$LatSites,$Z,$mean,$A[2],$A[3],$A[4],$A[5],5,$blue,$x0,$y0_map);	
//$RangeString=LabelString($parameter,$Z,$mean);



ImageGIF($im); ImageDestroy($im); 	


function SiteData($K,$LonSites,$LatSites,$Z,$Lon_min,$Lon_range,$Lat_max,$Lat_range,$lon_pix,$lat_pix) { 
// The input lon/lat data are from state boundaries.
// Returns array with (x,y,z,x,y,z,...) values, where x & y are pixel values scaled for mapping space
// based on state boundaries.		   
	$D=array();
	$x_scale=0.7; $y_scale=1.0;
	if ($Lon_range>=$Lat_range) $y_scale=$Lat_range/$Lon_range;
	if ($Lat_range>$Lon_range) $x_scale=0.7*$Lon_range/$Lat_range;
	$k=-1;
	for ($i=0; $i<$K; $i++) {
		$x=round($x_scale*($LonSites[$i]-$Lon_min)/$Lon_range*$lon_pix);
		$y=round($y_scale*($Lat_max-$LatSites[$i])/$Lat_range*$lat_pix);
		$k++; $D[$k]=$x; $k++; $D[$k]=$y; $k++; $D[$k]=$Z[$i];
	}
	return $D;
} 
?>
