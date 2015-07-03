<?php
/***************************************************************
*  hilbert curve
*  Version 0.3
*  
*  Copyright (c) 2010-2015 Chi Hoang 
*  All rights reserved
***************************************************************/
/*************************
*  hilbert_map_1  =  N
*  hilbert_map_9  =  S
*  hilbert_map_3  =  W
*  hilbert_map_7  =  E
*************************/

class hilbert {
	
	var $moore_map = array ( 	"0,0" => array ( 0, "hilbert_map_7"), 
								"0,1" => array ( 1, "hilbert_map_7"),
								"1,1" => array ( 2, "hilbert_map_3"),
								"1,0" => array ( 3, "hilbert_map_3"),
							);
					
	var $hilbert_map_1 = array (	'a' => array (
										"0, 0" => array (0, 'd'),
										"0, 1" => array (1, 'a'), 
										"1, 0" => array (3, 'b'),
										"1, 1" => array (2, 'a')
									), 
									 'b' => array ( 
										"0, 0" => array (2, 'b'), 
										"0, 1" => array (1, 'b'), 
										"1, 0" => array (3, 'a'),
										"1, 1" => array (0, 'c')
									), 
									'c' => array ( 
										"0, 0" => array (2, 'c'),
										"0, 1" => array (3, 'd'),
										"1, 0" => array (1, 'c'),
										"1, 1" => array (0, 'b')
									), 
									'd' => array (
										"0, 0" => array (0, 'a'), 
										"0, 1" => array (3, 'c'), 
										"1, 0" => array (1, 'd'), 
										"1, 1" => array (2, 'd')
									),
								);

	var $hilbert_map_7 = array ( 	'a' => array (
										"0, 0" => array (2, 'a'), 
										"0, 1" => array (1, 'a'), 
										"1, 0" => array (3, 'b'),
										"1, 1" => array (0, 'c')
									), 
									 'b' => array ( 
										"0, 0" => array (0, 'd'),
										"0, 1" => array (1, 'b'), 
										"1, 0" => array (3, 'a'),
										"1, 1" => array (2, 'b')
									), 
									'c' => array ( 
										"0, 0" => array (2, 'c'),
										"0, 1" => array (3, 'd'),
										"1, 0" => array (1, 'c'),
										"1, 1" => array (0, 'a')
									), 
									'd' => array (
										"0, 0" => array (0, 'b'), 
										"0, 1" => array (3, 'c'), 
										"1, 0" => array (1, 'd'), 
										"1, 1" => array (2, 'd')
									),
							);

	var $hilbert_map_3 = array ( 	'a' => array (
										"0, 0" => array (0, 'b'), 
										"0, 1" => array (3, 'c'), 
										"1, 0" => array (1, 'a'), 
										"1, 1" => array (2, 'a')
								), 
								 'b' => array ( 
								 		"0, 0" => array (0, 'a'),
										"0, 1" => array (1, 'b'), 
										"1, 0" => array (3, 'd'),
										"1, 1" => array (2, 'b')
									), 
								'c' => array ( 
										"0, 0" => array (2, 'c'),
										"0, 1" => array (3, 'a'),
										"1, 0" => array (1, 'c'),
										"1, 1" => array (0, 'd')
									), 
								'd' => array (
										 "0, 0" => array (2, 'd'), 
										 "0, 1" => array (1, 'd'), 
										 "1, 0" => array (3, 'b'),
										 "1, 1" => array (0, 'c')
								),
						);
			
	var $hilbert_map_9 = array ( 	'a' => array (
										"0, 0" => array (2, 'a'),
										"0, 1" => array (3, 'c'),
										"1, 0" => array (1, 'a'),
										"1, 1" => array (0, 'd')
								), 
								 'b' => array ( 
								 		"0, 0" => array (0, 'c'),
										"0, 1" => array (1, 'b'), 
										"1, 0" => array (3, 'd'),
										"1, 1" => array (2, 'b')
									), 
								'c' => array ( 
										"0, 0" => array (0, 'b'), 
										"0, 1" => array (3, 'a'), 
										"1, 0" => array (1, 'c'), 
										"1, 1" => array (2, 'c')
									), 
								'd' => array (
										 "0, 0" => array (2, 'd'), 
										 "0, 1" => array (1, 'd'), 
										 "1, 0" => array (3, 'b'),
										 "1, 1" => array (0, 'a')
								),
							);
			
    var $rev_map = Array ( 'a' => Array (
									  Array (3, 'd'),
									  Array (1, 'a'),
									  Array (0, 'a'),
									  Array (2, 'c')
					     ),
					     'b' => Array (
								  Array (0, 'c'),
								  Array (2, 'b'),
								  Array (3, 'b'),
								  Array (1, 'd')
					     ),
					     'c' => Array (
								  Array (0, 'b'),
								  Array (1, 'c'),
								  Array (3, 'c'),
								  Array (2, 'a')
					     ),
					     'd' => Array (
								  Array (3, 'a'),
								  Array (2, 'd'),
								  Array (0, 'd'),
								  Array (1, 'b')
					     ),
	);
	
	var $z_map = array       (  'a' => array (
									"0, 0" => array (1, 'a'),
									"0, 1" => array (3, 'a'), 
									"1, 0" => array (0, 'a'),
									"1, 1" => array (2, 'a')
							), 
				);
			
	function power($number,$base)
	{
		//use when the power is needed
		$pow=0;
		do {
		  $number=ceil($number/$base);
		  $pow++;
		} while ($number>1);
      
		if ($number==1)
		{
			return $pow;
		} else
		{
			return false;
		}
	}
   
	function point2quadkey($x, $y, $order=16, $map="hilbert_map_1", $mode="hilbert")
	{		
		switch ($mode)
		{
			case "moore":
			{	
				list( $moore, $map) = $this->point2moore ($x, $y, $order, "quadtree");
				//echo "\n$moore:$map\n";
				list( $x, $y) = $this->hilbert2point($moore, $order);
				//echo "\n$x:$y\n";
				$payload = $this->point2quadkey($x, $y, $order, $map);
				break;
			}
			default:
			{
				$current_square = 'a' ;
				$payload = 0; 
				foreach (range($order-1, 0, -1) as $i) { 
					$quad_x = $x & (1 << $i) ? 1 : 0;
					$quad_y = $y & (1 << $i) ? 1 : 0;
					list($quad_position, $current_square) = $this->{$map}[$current_square]["$quad_x, $quad_y"];
					$payload .= $quad_position;
				}
				break;
			}
		}
		return $payload;
	}

	function point2hilbert($x, $y, $order=16, $map="hilbert_map_1", $mode = "hilbert")
	{		
		$current_square = 'a' ;
		$position = 0; 
		foreach (range($order-1, 0, -1) as $i)
		{ 
			$position <<= 2; 
			$quad_x = $x & (1 << $i) ? 1 : 0;
			$quad_y = $y & (1 << $i) ? 1 : 0;
			list($quad_position, $current_square) = $this->{$map}[$current_square]["$quad_x, $quad_y"];
			$position |= $quad_position;
		}
		return $position;
	}
	
	function hilbert2point ( $hilbert, $order ) {
		$current_square = "a";
		$amount = 1 << $order - 1;
		$x = $y = 0;
		for ($i=2*$order;  $i>0; $i -= 2) {
			list ( $position, $current_square ) = $this->rev_map [ $current_square ] [ $hilbert >> $i - 2 ];
			switch ( $position ) {
				case 1:  $x+=$amount;
				break;
				case 2:  $y+=$amount;
				break;
				case 3:  $y+=$amount;
				         $x+=$amount;
				default: break;
			}
			$amount /= 2;
			$hilbert &=(1 << ($i - 2)) - 1;
		}
	}	
	
	function point2zorder($x, $y, $order=16)
	{
		$current_square = 'a' ;
		$position = 0; 
		foreach (range($order-1, 0, -1) as $i)
		{ 
			$position <<= 2; 
			$quad_x = $x & (1 << $i) ? 1 : 0;
			$quad_y = $y & (1 << $i) ? 1 : 0;
			list($quad_position, $current_square) = $this->z_map[$current_square]["$quad_x, $quad_y"];
			$position |= $quad_position;
		}
		return $position;
	}
		
		// points $x,$y should be 2^1 higher than $order
		// example $this->point2moore(7,7,2);
		// $this->point2moore(15,15,3);
	function point2moore($x, $y, $order=4, $mode="normal")
	{
		$quad = pow(2, $order)-1;
		switch ($order)
		{
			case 2:
			{
				$curve_length = 16;
				break;
			}
			case  1:
			{
				$curve_length = 8;
				break;
			}
			case 0:
			{
				$curve_length = 4;
				break;	
			}
			default:
			{
				$curve_length = pow(2, $quad)+1;
				break;
			}
		}
		
		$quad_x = $x & (1 << $order) ? 1 : 0;
		$quad_y = $y & (1 << $order) ? 1 : 0;
		
		list( $pos, $map ) = $this->moore_map[  "$quad_x,$quad_y" ];
		$pos *= $curve_length; 
		$quad_x *= $quad+1;
		$quad_y *= $quad+1;
		list( $px, $py ) =  array ( $x-$quad_x , $y-$quad_y );
		
		switch ($mode)
		{
			case "quadtree":
			{
				//echo "\n$pos:$x:$y:$order:$map:$curve_length\n";
				$payload = array ($pos - $this->point2hilbert($px, $py, $order, $map)-$curve_length, $map);
				break;
			}
			default:
			{
 				$payload =  $pos - $this->point2hilbert($px, $py, $order, $map)-$curve_length;
				break;
			}
		}
		return $payload;
	}
}
?>