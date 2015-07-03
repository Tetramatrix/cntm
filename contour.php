<?php

/*****************************************************************
 * Copyright notice
 *
 * (c) 2013-2015 Chi Hoang (info@chihoang.de)
 *  All rights reserved
 *
 ****************************************************************/
require_once("hilbert.php");

define("EPSILON",0.000001);
define("SUPER_TRIANGLE",(float)1000000000);

class Triangle {
   var $x,$y,$z;
   function __construct($x1,$y1,$x2,$y2,$x3,$y3,$x4,$y4,$x5,$y5,$x6,$y6) {
      $this->x=new Point(new Edge($x1,$y1),new Edge($x2,$y2));
      $this->y=new Point(new Edge($x3,$y3),new Edge($x4,$y4));
      $this->z=new Point(new Edge($x5,$y5),new Edge($x6,$y6));
   }
}

class Indices {
   var $x,$y,$z;
   function __construct($x=0,$y=0,$z=0) {
      $this->x=$x;
      $this->y=$y;
      $this->z=$z;
   }
}

class Edge
{
   var $e;
   function __construct($x,$y) {
      $this->e=new Point($x,$y);
   }
   
   public function __get($field) {
      if($field == 'x')
      {
	return $this->e->x;
      } else if($field == 'y')
      {
	 return $this->e->y;
      } else if($field == 'alpha') {
	 if($this->e->alpha==0) {
	    return rand(0,100);
	 } else {
	    return $this->e->alpha;
	 }
      }
   }
}

class Point
{
   var $x,$y,$z,$alpha;
   function __construct($x=0,$y=0,$z=0,$alpha=0) {
      $this->x=$x;
      $this->y=$y;
      $this->z=$z;
      $this->alpha=$alpha;
   }
}

  // circum circle
class Circle
{
   var $x, $y, $r, $r2;
   function Circle($x, $y, $r)
   {
      $this->x = $x;
      $this->y = $y;
      $this->r = $r;
   }
}


class Image
{
   var $stageWidth, $stageHeight, $padX, $padY, $delaunay, $average, $shape, $points, $indices;
   
   function __construct($pObj)
   {
      $this->stageWidth=$pObj->stageWidth;
      $this->stageHeight=$pObj->stageHeight;
      $this->padX=50;
      $this->padY=100;
      $this->delaunay=$pObj->delaunay;
      $this->average=$pObj->average;
      $this->shape=$pObj->shape;
      $this->svertx=$pObj->svertx;
      $this->sverty=$pObj->sverty;
      $this->points=$pObj->points;
      $this->indices=$pObj->indices;
      $this->mean=$pObj->mean;
   }
      
   function dotproduct($x1,$y1,$x2,$y2,$px,$py)
   {
      $dx1 = $x2-$x1;
      $dy1 = $y2-$y1;
      $dx2 = $px-$x1;
      $dy2 = $py-$y1;
      return ($dx1*$dy2)-($dy1*$dx2);
   }
   
	//http://stackoverflow.com/questions/30421985/line-segment-intersection
    function linesCross($x1,$y1,$x2,$y2,$x3,$y3,$x4,$y4) {
        //$denominator = (line1.end.y - line1.start.y) * (line2.end.x - line2.start.x) -
        //    (line1.end.x - line1.start.x) * (line2.end.y - line2.start.y)
   
		$denominator = ($y2-$y1)*($x4-$x3)-($x2-$x1)*($y4-$y3);
		  
		//lines are parallel 
		if ($denominator == 0) {
			return false;
		} 
   
       //let ua = ((line1.end.x - line1.start.x) * (line2.start.y - line1.start.y) -
       //    (line1.end.y - line1.start.y) * (line2.start.x - line1.start.x)) / denominator
       //let ub = ((line2.end.x - line2.start.x) * (line2.start.y - line1.start.y) -
       //    (line2.end.y - line2.start.y) * (line2.start.x - line1.start.x)) / denominator
   
       $ua = (($x2-$x1)*($y3-$y1)-($y2-$y1)*($x3-$x1)) / $denominator;
       $ub = (($x4-$x3)*($y3-$y1)-($y4-$y3)*($x3-$x1)) / $denominator;
	   
       //lines may touch each other - no test for equality here
       return $ua > 0 && $ua < 1 && $ub > 0 && $ub < 1;
    }
   
	// http://www.ecse.rpi.edu/~wrf/Research/Short_Notes/pnpoly.html
	function pnpoly($nvert, $vertx, $verty, $testx, $testy)
	{
		$i=$j=$c=0;
		for ($i=0, $j=$nvert-1; $i<$nvert; $j=$i++)
		{
			if ((($verty[$i]>$testy) != ($verty[$j]>$testy)) &&
				($testx < ($vertx[$j]-$vertx[$i]) * ($testy-$verty[$i])/($verty[$j]-$verty[$i]) + $vertx[$i]))
			{
				$c= !$c;
			}
		}
		return $c;
	}
   
	function draw($im)
	{	
		$white = imagecolorallocate ($im,0xff,0xff,0xff);
		$black = imagecolorallocate($im,0x00,0x00,0x00);
		$grey_lite = imagecolorallocate ($im,0xee,0xee,0xee);
		$grey_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
		$firebrick = imagecolorallocate ($im,0xb2,0x22,0x22);
		$blue = imagecolorallocate ($im,0x00,0x00,0xff);
		$darkorange = imagecolorallocate ($im,0xff,0x8c,0x00);
		$red = imagecolorallocate ($im,0xff,0x00,0x00);
		$purple = imagecolorallocate ($im,0x80,0x00,0x80);
      
		foreach ($this->delaunay as $key => $arr)
		{
			$c=0;
			foreach ($arr as $ikey => $iarr)
			{
				list($x1,$y1,$x2,$y2)=array($iarr->x->x,$iarr->x->y,$iarr->y->x,$iarr->y->y);
				$dx=$x2-$x1;
				$dy=$y2-$y1;
				$d=$dx*$dx+$dy*$dy;
			   
				$ok=0;
				foreach ($this->svertx as $part => $val)
				{
					$k=count($this->svertx[$part]);
					$ok=$this->pnpoly($k,$this->svertx[$part],$this->sverty[$part],$x1,$y1);
					$ok=$this->pnpoly($k,$this->svertx[$part],$this->sverty[$part],$x2,$y2);
					if ($ok) break;
				}
	    
	    	    //http://stackoverflow.com/questions/1934210/finding-a-point-on-a-line
				//	    $temp=sqrt($d);
				//	    $r=2/$temp;
				//	    $x3=$r*$x2+(1-$r)*$x1;
				//	    $y3=$r*$y2+(1-$r)*$y1;
				//	    if (!$this->pnpoly($ns,$this->svertx,$this->sverty,$x3,$y3)) {
				//	       ++$c;
				//            }
				//	    
				//	    $r=3/$temp;
				//	    $x3=$r*$x2+(1-$r)*$x1;
				//	    $y3=$r*$y2+(1-$r)*$y1;
				//	    if (!$this->pnpoly($ns,$this->svertx,$this->sverty,$x3,$y3)) {
				//	       ++$c;
				//            }

				for ($i=0,$end=count($this->svertx[$part]);$i<$end;$i+=2)
				{
					if ($this->linesCross($this->svertx[$part][$i],$this->sverty[$part][$i],
								  $this->svertx[$part][$i+1],$this->sverty[$part][$i+1],
									$x1,$y1,$x2,$y2))
					{
						//++$c;
						$c+=1.2;
						break;
					}
				}
				
				//if (!$this->pnpoly($ns,$this->svertx,$this->sverty,($x1+$x2)/2-10,($y1+$y2)/2-10)) {
				//   ++$c;
				//}
				//if (!$this->pnpoly($ns,$this->svertx,$this->sverty,($x1+$x2)/2+10,($y1+$y2)/2+10)) {
				//   ++$c;
				//}
	    
				if (!$this->pnpoly(count($this->svertx[$part]),
								   $this->svertx[$part],$this->sverty[$part],
								   ($x1+$x2)/2,($y1+$y2)/2))
				{
					$c+=4;
				}
	
				if ($c==0 && $ok && abs($x1)!=SUPER_TRIANGLE &&
					abs($y1)!=SUPER_TRIANGLE && abs($x2)!=SUPER_TRIANGLE && abs($y2)!=SUPER_TRIANGLE)
				{
				   $points[$key][]=$arr->$ikey->x->x+$this->padX;
				   $points[$key][]=$arr->$ikey->x->y+$this->padY;
				   $subject[$key][$ikey]=$this->indices[$key]->$ikey;
				
				} else if ($c>1 && ($d*$c)<$this->average && abs($x1)!=SUPER_TRIANGLE &&
						abs($y1)!=SUPER_TRIANGLE && abs($x2)!=SUPER_TRIANGLE && abs($y2)!=SUPER_TRIANGLE)
				{   
				   $points[$key][]=$arr->$ikey->x->x+$this->padX;
				   $points[$key][]=$arr->$ikey->x->y+$this->padY;
				   $subject[$key][$ikey]=$this->indices[$key]->$ikey;
				}
			}
		}

	    $triangles=0;
		foreach ($points as $key=>$arr) {
			if (count($arr)>=6 && count($subject[$key])==3) {
				++$triangles;
				
				$arr=array_values($arr);
				$averageX=($this->points[$subject[$key]["x"]]->alpha+$this->points[$subject[$key]["y"]]->alpha+$this->points[$subject[$key]["z"]]->alpha)/3;
				
				$zx = $this->points[$subject[$key]["x"]]->z;
				$zy = $this->points[$subject[$key]["y"]]->z;
				$zz = $this->points[$subject[$key]["z"]]->z;
		   
				if ($zx<0 && $zy<0 && $zz<0) goto triangleEnd;
			
				$find=array($zx,$zy,$zz);
				for ($i=0;$i<3;$i++) {
					for ($j=0;$j<3;$j++) {
						if ($i!=$j && $find[$i]<0 && $find[$j]>0 ) {
							$find[$i]=$find[$j];
						}
					}
				}
				list($zx,$zy,$zz)=$find;
				$averageZ=($zx+$zy+$zz)/3;
			
				$delta=min((max($averageX,$averageZ)-min($averageX,$averageZ))*(255/STEPS),190);
				list($r,$g,$b)=$averageX>$averageZ ? array(190-$delta,190-$delta,255) : array(255,190-$delta,190-$delta);
				$col= imagecolorstotal($im)>=255 ? imagecolorclosest($im,$r,$g,$b) : imagecolorallocate($im,$r,$g,$b);
 
				//imagefilledellipse($im,$arr[$i],$arr[$i+1], 4, 4, $darkorange);
				//imagefilledellipse($im,$arr[$i+2],$arr[$i+3], 4, 4, $darkorange);
				//imagefilledellipse($im,($arr[$i]+$arr[$i+2])/2,($arr[$i+1]+$arr[$i+3])/2, 4, 4, $darkorange);
		
				imagefilledpolygon($im,$arr,count($arr)/2,$col);
	    
				goto triangleEnd;
	
				for ($i=0,$e=count($arr);$i<$e;$i+=2) {
				   list($x1,$y1,$x2,$y2)=array($arr[$i],$arr[$i+1],$arr[$i+2],$arr[$i+3]);
				   if ($x1!=0 && $y1!=0 && $x2!=0 && $y2!=0) {
					  //imagefilledellipse($im,$arr[$i],$arr[$i+1], 4, 4, $black);
					  imageline($im,$arr[$i],$arr[$i+1],$arr[$i+2],$arr[$i+3],$grey_dark);
				   }
				}
				imageline($im,$arr[0],$arr[1],$arr[$i-2],$arr[$i-1],$grey_dark);	    
triangleEnd:	    
			}
		}
      
		if (!$triangles) {	
			foreach ($this->delaunay as $key => $arr)
			{
				$c=0;
				foreach ($arr as $ikey => $iarr)
				{
					list($x1,$y1,$x2,$y2)=array($iarr->x->x,$iarr->x->y,$iarr->y->x,$iarr->y->y);
					$dx=$x2-$x1;
					$dy=$y2-$y1;
					$d=$dx*$dx+$dy*$dy*15;
			
					if ($d<$this->average && abs($x1)!=SUPER_TRIANGLE && abs($y1)!=SUPER_TRIANGLE && abs($x2)!=SUPER_TRIANGLE && abs($y2)!=SUPER_TRIANGLE )
					{
					   $points[$key][]=$arr->$ikey->x->x+$this->padX;
					   $points[$key][]=$arr->$ikey->x->y+$this->padY;
					   $subject[$key][$ikey]=$this->indices[$key]->$ikey;			
					} 
				}
			}
			
			$triangles=0;
			foreach ($points as $key=>$arr) {
				
				if (count($arr)>=6  && count($subject[$key])==3) {
					++$triangles;
				
					$arr=array_values($arr);
					$averageX=($this->points[$subject[$key]["x"]]->alpha+$this->points[$subject[$key]["y"]]->alpha+$this->points[$subject[$key]["z"]]->alpha)/3;
					
					$zx = $this->points[$subject[$key]["x"]]->z;
					$zy = $this->points[$subject[$key]["y"]]->z;
					$zz = $this->points[$subject[$key]["z"]]->z;
					
					if ($zx<0 && $zy<0 && $zz<0) goto triangleEnd2;
					
					$find=array($zx,$zy,$zz);
					for ($i=0;$i<3;$i++) {
						for ($j=0;$j<3;$j++) {
							if ($i!=$j && $find[$i]<0 && $find[$j]>0 ) {
								$find[$i]=$find[$j];
							}
						}
					}
					list($zx,$zy,$zz)=$find;
					$averageZ=($zx+$zy+$zz)/3;
			
					$delta=min((max($averageX,$averageZ)-min($averageX,$averageZ))*(255/STEPS),190);
					list($r,$g,$b)=$averageX>$averageZ ? array(190-$delta,190-$delta,255) : array(255,190-$delta,190-$delta);
					$col= imagecolorstotal($im)>=255 ? imagecolorclosest($im,$r,$g,$b) : imagecolorallocate($im,$r,$g,$b);
			
					//imagefilledellipse($im,$arr[$i],$arr[$i+1], 4, 4, $darkorange);
					//imagefilledellipse($im,$arr[$i+2],$arr[$i+3], 4, 4, $darkorange);
					//imagefilledellipse($im,($arr[$i]+$arr[$i+2])/2,($arr[$i+1]+$arr[$i+3])/2, 4, 4, $darkorange);
				   
					imagefilledpolygon($im,$arr,count($arr)/2,$col);
				   
					goto triangleEnd2;
				   
					for ($i=0,$e=count($arr);$i<$e;$i+=2) {
					   list($x1,$y1,$x2,$y2)=array($arr[$i],$arr[$i+1],$arr[$i+2],$arr[$i+3]);
					   if ($x1!=0 && $y1!=0 && $x2!=0 && $y2!=0) {
						  //imagefilledellipse($im,$arr[$i],$arr[$i+1], 4, 4, $black);
						  imageline($im,$arr[$i],$arr[$i+1],$arr[$i+2],$arr[$i+3],$grey_dark);
					   }
					}
					imageline($im,$arr[0],$arr[1],$arr[$i-2],$arr[$i-1],$grey_dark);	    
triangleEnd2:	    
				}
			}
		}

	    goto drawEnd;
      
	    for ($i=0,$end=count($this->shape);$i<$end;$i+=2) {
			list($x1,$y1)=$this->shape[$i];
			list($x2,$y2)=$this->shape[$i+1];
			$dx=$x2-$x1;
			$dy=$y2-$y1;
			$d=$dx*$dx+$dy*$dy;
			if ($d<$this->average)
			{
			   imageline($im,$x1+$this->padX,$y1+$this->padY,
			   $x2+$this->padX,$y2+$this->padY,
			   $black);
			}
		}
drawEnd:
   }
}

class Contourplot
{
	var $stageWidth = 400;
	var $stageHeight = 400;
	var $delaunay = array();
	var $points = array();
	var $indices = array();
	var $cc = array();
   
	function CircumCircle($x1,$y1,$x2,$y2,$x3,$y3)
	{
		$mx2=$my2=0;
		
		//list($x1,$y1)=array(1,3);
		//list($x2,$y2)=array(6,5);
		//list($x3,$y3)=array(4,7);
      
		$absy1y2 = abs($y1-$y2);
		$absy2y3 = abs($y2-$y3);

		if ($absy1y2 < EPSILON)
		{
			if ($absy2y3 < EPSILON) {
			   $y3+=EPSILON;
			}
			$m2 = -($x3-$x2) / ($y3-$y2);
			$mx2 = ($x2 + $x3) / 2.0;
			$my2 = ($y2 + $y3) / 2.0;
			$xc = ($x2 + $x1) / 2.0;
			$yc = $m2 * ($xc - $mx2) + $my2;
		}
		else if ($absy2y3 < EPSILON)
		{
			if ($absy1y2 < EPSILON) {
			   $y2+=EPSILON;
			}
			$m1 = -($x2-$x1) / ($y2-$y1);
			$mx1 = ($x1 + $x2) / 2.0;
			$my1 = ($y1 + $y2) / 2.0;
			
			$xc = ($x3 + $x2) / 2.0;
			$yc = $m1*($xc - $mx1) + $my1;	
		}
		else 
		{
			$m1 = -($x2-$x1) / ($y2-$y1);
			$m2 = -($x3-$x2) / ($y3-$y2);
			
			if (($m1-$m2)==0)
			{
			   $mx1 = ($x1 + $x2) / 2.0;
			   $my1 = ($y1 + $y2) / 2.0;
			   $xc = ($x3 + $x2) / 2.0;
			} else
			{   
			   $mx1 = ($x1 + $x2) / 2.0;
			   $mx2 = ($x2 + $x3) / 2.0;
			   $my1 = ($y1 + $y2) / 2.0;
			   $my2 = ($y2 + $y3) / 2.0;
			   $xc = ($m1*$mx1 - $m2*$mx2 + $my2 - $my1) / ($m1 - $m2);
			}
         
			if ($absy1y2 > $absy2y3)
			{
			   $yc = $m1 * ($xc - $mx1) + $my1;   
			} else
			{
			   $yc = $m2 * ($xc - $mx2) + $my2;   
			}
		}
      
		$dx = $x2 - $xc;
		$dy = $y2 - $yc;
		$rsqr = $dx*$dx + $dy*$dy;
		//$r = sqrt($rsqr);
     
		return new Circle($xc, $yc, $rsqr);
	}

	function inside(Circle $c, $x, $y)
	{
		$dx = $x - $c->x;
		$dy = $y - $c->y;
		$drsqr = $dx*$dx + $dy*$dy;
		$inside = (($drsqr-$c->r) <= EPSILON) ? true : false;
		return $inside;
	}
   
   function getEdges($n, $points)
   {
		/*
		   Set up the supertriangle
		   This is a triangle which encompasses all the sample points.
		   The supertriangle coordinates are added to the end of the
		   vertex list. The supertriangle is the first triangle in
		   the triangle list.
		*/
      
		$points[$n+0] = new Point(-SUPER_TRIANGLE,SUPER_TRIANGLE);
		$points[$n+1] = new Point(0,-SUPER_TRIANGLE);
		$points[$n+2] = new Point(SUPER_TRIANGLE,SUPER_TRIANGLE);
    
		// indices       
		$v = array(); 
		$v[] = new Indices($n,$n+1,$n+2);
      
		//sort buffer
		$complete = array();
		$complete[] = false;
      
		/*
		Include each point one at a time into the existing mesh
		*/
		foreach ($points as $key => $arr)
		{        
			/*
			   Set up the edge buffer.
			   If the point (xp,yp) lies inside the circumcircle then the
			   three edges of that triangle are added to the edge buffer
			   and that triangle is removed.
			*/
		
			$edges=array();
			foreach ($v as $vkey => $varr)
			{  
				if ($complete[$vkey]) continue;
				list($vi,$vj,$vk)=array($v[$vkey]->x,$v[$vkey]->y,$v[$vkey]->z);
				$c=$this->CircumCircle($points[$vi]->x,$points[$vi]->y,
					  $points[$vj]->x,$points[$vj]->y,
					  $points[$vk]->x,$points[$vk]->y);
				if ($c->x + $c->r < $points[$key]->x) $complete[$vkey]=1;
				if ($c->r > EPSILON && $this->inside($c, $points[$key]->x,$points[$key]->y))
				{
					$edges[]=new Edge($vi,$vj);
					$edges[]=new Edge($vj,$vk);
					$edges[]=new Edge($vk,$vi); 
					unset($v[$vkey]);
					unset($complete[$vkey]);
				}
			}
         
			/*
			   Tag multiple edges
			   Note: if all triangles are specified anticlockwise then all
			   interior edges are opposite pointing in direction.
			*/
			$edges=array_values($edges);
			foreach ($edges as $ekey => $earr)
			{   
			   foreach ($edges as $ikey => $iarr)
			   {
				  if ($ekey != $ikey)
				  {
					if (($earr->x == $iarr->y) && ($earr->y == $iarr->x))
					{
					   unset($edges[$ekey]);
					   unset($edges[$ikey]);
					   
					} else if (($earr->x == $iarr->x) && ($earr->y == $iarr->y))
					{
					   unset($edges[$ekey]);
					   unset($edges[$ikey]);   
					}
				  }
			   }
			}
         
			/*
			   Form new triangles for the current point
			   Skipping over any tagged edges.
			   All edges are arranged in clockwise order.
			*/
			$complete=array_values($complete);
			$v=array_values($v);
			$ntri=count($v);
			$edges=array_values($edges);
			foreach ($edges as $ekey => $earr)
			{
				if ($edges[$ekey]->x != $key && $edges[$ekey]->y != $key)
				{
				  $v[] = new Indices($edges[$ekey]->x,$edges[$ekey]->y,$key);
				}
				$complete[$ntri++]=0;
			}
		}
    
		foreach ($v as $key => $arr)
		{
			$this->indices[$key]=$arr;
			$this->delaunay[$key]=new Triangle($points[$arr->x]->x,$points[$arr->x]->y,
						 $points[$arr->y]->x,$points[$arr->y]->y,
						 $points[$arr->y]->x,$points[$arr->y]->y,
						 $points[$arr->z]->x,$points[$arr->z]->y,
						 $points[$arr->z]->x,$points[$arr->z]->y,
						 $points[$arr->x]->x,$points[$arr->x]->y                                 
					  );
			
			$dx=$points[$arr->y]->x-$points[$arr->x]->x;
			$dy=$points[$arr->y]->y-$points[$arr->x]->y;
			$this->dist[$key][]=$dx*$dx+$dy*$dy;
			
			$dx=$points[$arr->z]->x-$points[$arr->y]->x;
			$dy=$points[$arr->z]->y-$points[$arr->y]->y;
			$this->dist[$key][]=$dx*$dx+$dy*$dy;
			   
			$dx=$points[$arr->x]->x-$points[$arr->z]->x;
			$dy=$points[$arr->x]->y-$points[$arr->z]->y;
			$this->dist[$key][]=$dx*$dx+$dy*$dy;
		}
		return count($v);
	}
   
   function main($points=0,$stageWidth=400,$stageHeight=400,$shape=0,$mean,$weight=6.899)
   {
		$this->stageWidth = $stageWidth;
		$this->stageHeight = $stageHeight;
		$this->delaunay = array();
		$this->pointset = array();
		$this->indices = array();
		$this->weight = $weight;
		$this->shape = array();
		$this->mean = $mean;
		
		$part=$shape[1];
		$x1=$shape[0][0]; $y1=$shape[0][1];
		$k=0;
		for ($i=3,$end=sizeof($shape[0]); $i<$end; $i+=2) {	
			$k++; 
			if ($part[$k] == $part[$k-1]) {
			   $this->shape[$part[$k]][]=array($x1,$y1);
			   $this->shape[$part[$k]][]=array($shape[0][$i-1],$shape[0][$i]);
			   $x1=$shape[0][$i-1];
			   $y1=$shape[0][$i];
			} 
		}

		//pnpoly shape
		$this->svertx=$this->sverty=array(); 
		foreach($this->shape as $key => $arr)
		{
			foreach ($arr as $ikey => $iarr)
			{
				list($this->svertx[$key][],$this->sverty[$key][])=$iarr;
			}
		}
		
		if (!empty($points))
		{
			goto format;
			$sum=$c=0;
			for ($i=0,$end=count($points);$i<$end;$i+=3) {
			   $sum+=$points[$i+2];
			   ++$c;
			}
			$this->mean=$mean=$sum/$c;
format:
			for ($i=0,$end=count($points);$i<$end;$i+=3) {
				$this->points[]=new Point($points[$i],$points[$i+1],$points[$i+2],$this->mean);   
			}
		}
      
		//goto hilbert;
		
		$x=$y=$sortX=array(); 
		foreach($this->points as $key=>$arr)
		{
			$sortX[$key]=$arr->x;
		} 
		array_multisort($sortX, SORT_ASC, SORT_NUMERIC, $this->points);
		goto dt;
      
hilbert:
		$sortX=array(); 
		$maxx=$maxy=0;
		foreach ($this->points as $key => $arr)
		{
		  if ($maxx<$arr->x) $maxx=$arr->y;
		  if ($maxy<$arr->y) $maxy=$arr->y;
		}
      
		$hilbert = new hilbert();     
		$powx=$hilbert->power($maxx,2);     
		$powy=$hilbert->power($maxy,2);
		$order= ($powx<$powy) ? $powy : $powx;
   
		foreach($this->points as $key => $arr) {
		   $sort[$key] = $hilbert->point2hilbert($arr->x, $arr->y, $order);
		}
		array_multisort($sort, SORT_DESC, SORT_NUMERIC, $this->points);
dt:
		$result=$this->getEdges(count($this->points), $this->points);
	 
		$sum=$c=0;
		foreach ($this->dist as $key => $arr)
		{
		  if (array_sum($arr)<SUPER_TRIANGLE)
		  {
			 $sum+=array_sum($arr);
			 $c+=count($arr);   
		  }
		}
		$this->average=($sum/$c)*$this->weight;
		return $result;
	}
}
?>