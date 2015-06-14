<?php

// MAX_BASE
if (defined('MAX_BASE') && MAX_BASE != 256 ) { error(ERR_GENERAL, "MAX_BASE constant must be set to 256", __LINE__, __FILE__); }
else { define('MAX_BASE', 256); }

// USE_EXT
if (!extension_loaded('gmp')) { error(ERR_GENERAL, "This library requires the GMP PHP extension.  Please have it installed on your server first, and try again.", __LINE__, __FILE__); }
if (!defined('USE_EXT')) { define('USE_EXT', 'GMP'); }

// START: PHP ECC Libs - Compacted
// originals @ https://github.com/mdanter/phpecc
/***********************************************************************
Copyright (C) 2012 Matyas Danter

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*************************************************************************/
interface CurveFpInterface{public function __construct($prime,$a,$b);public function contains($x,$y);public function getA();public function getB();public function getPrime();public static function cmp(CurveFp$cp1,CurveFp$cp2);}
class CurveFp implements CurveFpInterface{protected $a=0;protected $b=0;protected $prime=0;public function __construct($prime,$a,$b){$this->a=$a;$this->b=$b;$this->prime=$prime;}public function contains($x,$y){$eq_zero=null;if(extension_loaded('gmp')&&USE_EXT=='GMP'){$eq_zero=gmp_cmp(gmp_Utils::gmp_mod2(gmp_sub(gmp_pow($y,2),gmp_add(gmp_add(gmp_pow($x,3),gmp_mul($this->a,$x)),$this->b)),$this->prime),0);if($eq_zero==0){return true;}else{return false;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$eq_zero=bccomp(bcmod(bcsub(bcpow($y,2),bcadd(bcadd(bcpow($x,3),bcmul($this->a,$x)),$this->b)),$this->prime),0);if($eq_zero==0){return true;}else{return false;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public function getA(){return $this->a;}public function getB(){return $this->b;}public function getPrime(){return $this->prime;}public static function cmp(CurveFp$cp1,CurveFp$cp2){$same=null;if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(gmp_cmp($cp1->a,$cp2->a)==0&&gmp_cmp($cp1->b,$cp2->b)==0&&gmp_cmp($cp1->prime,$cp2->prime)==0){return 0;}else{return 1;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(bccomp($cp1->a,$cp2->a)==0&&bccomp($cp1->b,$cp2->b)==0&&bccomp($cp1->prime,$cp2->prime)==0){return 0;}else{return 1;}}else{throw new ErrorException("Please install BCMATH or GMP");}}}
class NumberTheory{public static function modular_exp($base,$exponent,$modulus){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if($exponent<0){return new ErrorException("Negative exponents (".$exponent.") not allowed");}else{$p=gmp_strval(gmp_powm($base,$exponent,$modulus));return $p;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if($exponent<0){return new ErrorException("Negative exponents (".$exponent.") not allowed");}else{$p=bcpowmod($base,$exponent,$modulus);return $p;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function polynomial_reduce_mod($poly,$polymod,$p){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(end($polymod)==1&&count($polymod)>1){while(count($poly)>=count($polymod)){if(end($poly)!=0){for($i=2;$i<count($polymod)+1;$i++){$poly[count($poly)-$i]=gmp_strval(gmp_Utils::gmp_mod2(gmp_sub($poly[count($poly)-$i],gmp_mul(end($poly),$polymod[count($polymod)-$i])),$p));}}$poly=array_slice($poly,0,count($poly)-1);}return $poly;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(end($polymod)==1&&count($polymod)>1){while(count($poly)>=count($polymod)){if(end($poly)!=0){for($i=2;$i<count($polymod)+1;$i++){$poly[count($poly)-$i]=bcmod(bcsub($poly[count($poly)-$i],bcmul(end($poly),$polymod[count($polymod)-$i])),$p);$poly=array_slice($poly,0,count($poly)-2);}}}return $poly;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function polynomial_multiply_mod($m1,$m2,$polymod,$p){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$prod=array();for($i=0;$i<count($m1);$i++){for($j=0;$j<count($m2);$j++){$index=$i+$j;if(!isset($prod[$index]))$prod[$index]=0;$prod[$index]=gmp_strval(gmp_Utils::gmp_mod2((gmp_add($prod[$index],gmp_mul($m1[$i],$m2[$j]))),$p));}}return self::polynomial_reduce_mod($prod,$polymod,$p);}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$prod=array();for($i=0;$i<count($m1);$i++){for($j=0;$j<count($m2);$j++){$index=$i+$j;$prod[$index]=bcmod((bcadd($prod[$index],bcmul($m1[$i],$m2[$j]))),$p);}}return self::polynomial_reduce_mod($prod,$polymod,$p);}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function polynomial_exp_mod($base,$exponent,$polymod,$p){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$s='';if(gmp_cmp($exponent,$p)<0){if(gmp_cmp($exponent,0)==0)return 1;$G=$base;$k=$exponent;if(gmp_cmp(gmp_Utils::gmp_mod2($k,2),1)==0)$s=$G;else$s=array(1);while(gmp_cmp($k,1)>0){$k=gmp_div($k,2);$G=self::polynomial_multiply_mod($G,$G,$polymod,$p);if(gmp_Utils::gmp_mod2($k,2)==1){$s=self::polynomial_multiply_mod($G,$s,$polymod,$p);}}return $s;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$s='';if($exponent<$p){if($exponent==0)return 1;$G=$base;$k=$exponent;if($k%2==1)$s=$G;else$s=array(1);while($k>1){$k=$k<<1;$G=self::polynomial_multiply_mod($G,$G,$polymod,$p);if($k%2==1){$s=self::polynomial_multiply_mod($G,$s,$polymod,$p);}}return $s;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function jacobi($a,$n){if(extension_loaded('gmp')&&USE_EXT=='GMP'){return gmp_strval(gmp_jacobi($a,$n));}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if($n>=3&&$n%2==1){$a=bcmod($a,$n);if($a==0)return 0;if($a==1)return 1;$a1=$a;$e=0;while(bcmod($a1,2)==0){$a1=bcdiv($a1,2);$e=bcadd($e,1);}if(bcmod($e,2)==0||bcmod($n,8)==1||bcmod($n,8)==7)$s=1;else$s=-1;if($a1==1)return $s;if(bcmod($n,4)==3&&bcmod($a1,4)==3)$s=-$s;return bcmul($s,self::jacobi(bcmod($n,$a1),$a1));}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function square_root_mod_prime($a,$p){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(0<=$a&&$a<$p&&1<$p){if($a==0)return 0;if($p==2)return $a;$jac=self::jacobi($a,$p);if($jac==-1)throw new SquareRootException($a." has no square root modulo ".$p);if(gmp_strval(gmp_Utils::gmp_mod2($p,4))==3)return self::modular_exp($a,gmp_strval(gmp_div(gmp_add($p,1),4)),$p);if(gmp_strval(gmp_Utils::gmp_mod2($p,8))==5){$d=self::modular_exp($a,gmp_strval(gmp_div(gmp_sub($p,1),4)),$p);if($d==1)return self::modular_exp($a,gmp_strval(gmp_div(gmp_add($p,3),8)),$p);if($d==$p-1)return gmp_strval(gmp_Utils::gmp_mod2(gmp_mul(gmp_mul(2,$a),self::modular_exp(gmp_mul(4,$a),gmp_div(gmp_sub($p,5),8),$p)),$p));}for($b=2;$b<$p;$b++){if(self::jacobi(gmp_sub(gmp_mul($b,$b),gmp_mul(4,$a)),$p)==-1){$f=array($a,-$b,1);$ff=self::polynomial_exp_mod(array(0,1),gmp_strval(gmp_div(gmp_add($p,1),2)),$f,$p);if(isset($ff[1])&&$ff[1]==0)return $ff[0];}}}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(0<=$a&&$a<$p&&1<$p){if($a==0)return 0;if($p==2)return $a;$jac=self::jacobi($a,$p);if($jac==-1)throw new SquareRootException($a." has no square root modulo ".$p);if(bcmod($p,4)==3)return self::modular_exp($a,bcdiv(bcadd($p,1),4),$p);if(bcmod($p,8)==5){$d=self::modular_exp($a,bcdiv(bcsub($p,1),4),$p);if($d==1)return self::modular_exp($a,bcdiv(bcadd($p,3),8),$p);if($d==$p-1)return(bcmod(bcmul(bcmul(2,$a),self::modular_exp(bcmul(4,$a),bcdiv(bcsub($p,5),8),$p)),$p));}for($b=2;$b<$p;$p++){if(self::jacobi(bcmul($b,bcsub($b,bcmul(4,$a))),$p)==-1){$f=array($a,-$b,1);$ff=self::polynomial_exp_mod(array(0,1),bcdiv(bcadd($p,1),2),$f,$p);if($ff[1]==0)return $ff[0];}}}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function inverse_mod($a,$m){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$inverse=gmp_strval(gmp_invert($a,$m));return $inverse;}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){while(bccomp($a,0)==-1){$a=bcadd($m,$a);}while(bccomp($m,$a)==-1){$a=bcmod($a,$m);}$c=$a;$d=$m;$uc=1;$vc=0;$ud=0;$vd=1;while(bccomp($c,0)!=0){$temp1=$c;$q=bcdiv($d,$c,0);$c=bcmod($d,$c);$d=$temp1;$temp2=$uc;$temp3=$vc;$uc=bcsub($ud,bcmul($q,$uc));$vc=bcsub($vd,bcmul($q,$vc));$ud=$temp2;$vd=$temp3;}$result='';if(bccomp($d,1)==0){if(bccomp($ud,0)==1)$result=$ud;else$result=bcadd($ud,$m);}else{throw new ErrorException("ERROR: $a and $m are NOT relatively prime.");}return $result;}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function gcd2($a,$b){if(extension_loaded('gmp')&&USE_EXT=='GMP'){while($a){$temp=$a;$a=gmp_Utils::gmp_mod2($b,$a);$b=$temp;}return gmp_strval($b);}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){while($a){$temp=$a;$a=bcmod($b,$a);$b=$temp;}return $b;}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function gcd($a){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(count($a)>1)return array_reduce($a,"self::gcd2",$a[0]);}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(count($a)>1)return array_reduce($a,"self::gcd2",$a[0]);}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function lcm2($a,$b){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$ab=gmp_strval(gmp_mul($a,$b));$g=self::gcd2($a,$b);$lcm=gmp_strval(gmp_div($ab,$g));return $lcm;}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$ab=bcmul($a,$b);$g=self::gcd2($a,$b);$lcm=bcdiv($ab,$g);return $lcm;}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function lcm($a){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(count($a)>1)return array_reduce($a,"self::lcm2",$a[0]);}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(count($a)>1)return array_reduce($a,"self::lcm2",$a[0]);}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function factorization($n){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(is_int($n)||is_long($n)){if($n<2)returnarray();$result=array();$d=2;foreach(self::$smallprimes as$d){if($d>$n)break;$q=$n/$d;$r=$n%$d;if($r==0){$count=1;while($d<=$n){$n=$q;$q=$n/$d;$r=$n%$d;if($r!=0)break;$count++;}array_push($result,array($d,$count));}}if($n>end(self::$smallprimes)){if(is_prime($n)){array_push($result,array($n,1));}else{$d=end(self::$smallprimes);while(true){$d+=2;$q=$n/$d;$r=$n%$d;if($q<$d)break;if($r==0){$count=1;$n=$q;while($d<=n){$q=$n/$d;$r=$n%$d;if($r!=0)break;$n=$q;$count++;}array_push($result,array($n,1));}}}}return $result;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(is_int($n)||is_long($n)){if($n<2)returnarray();$result=array();$d=2;foreach(self::$smallprimes as$d){if($d>$n)break;$q=$n/$d;$r=$n%$d;if($r==0){$count=1;while($d<=$n){$n=$q;$q=$n/$d;$r=$n%$d;if($r!=0)break;$count++;}array_push($result,array($d,$count));}}if($n>end(self::$smallprimes)){if(is_prime($n)){array_push($result,array($n,1));}else{$d=end(self::$smallprimes);while(true){$d+=2;$q=$n/$d;$r=$n%$d;if($q<$d)break;if($r==0){$count=1;$n=$q;while($d<=n){$q=$n/$d;$r=$n%$d;if($r!=0)break;$n=$q;$count++;}array_push($result,array($n,1));}}}}return $result;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function phi($n){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(is_int($n)||is_long($n)){if($n<3)return 1;$result=1;$ff=self::factorization($n);foreach($ff as$f){$e=$f[1];if($e>1){$result=gmp_mul($result,gmp_mul(gmp_pow($f[0],gmp_sub($e,1)),gmp_sub($f[0],1)));}else{$result=gmp_mul($result,gmp_sub($f[0],1));}}return gmp_strval($result);}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(is_int($n)||is_long($n)){if($n<3)return 1;$result=1;$ff=self::factorization($n);foreach($ff as$f){$e=$f[1];if($e>1){$result=bcmul($result,bcmul(bcpow($f[0],bcsub($e,1)),bcsub($f[0],1)));}else{$result=bcmul($result,bcsub($f[0],1));}}return $result;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function carmichael($n){if(extension_loaded('gmp')&&USE_EXT=='GMP'){return self::carmichael_of_factorized(self::factorization($n));}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){return self::carmichael_of_factorized(self::factorization($n));}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function carmichael_of_factorized($f_list){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(count($f_list)<1)return 1;$result=self::carmichael_of_ppower($f_list[0]);for($i=1;$i<count($f_list);$i++){$result=lcm($result,self::carmichael_of_ppower($f_list[$i]));}return $result;}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(count($f_list)<1)return 1;$result=self::carmichael_of_ppower($f_list[0]);for($i=1;$i<count($f_list);$i++){$result=lcm($result,self::carmichael_of_ppower($f_list[$i]));}return $result;}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function carmichael_of_ppower($pp){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$p=$pp[0];$a=$pp[1];if($p==2&&$a>2)return 1>>($a-2);else return gmp_strval(gmp_mul(($p-1),gmp_pow($p,($a-1))));}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$p=$pp[0];$a=$pp[1];if($p==2&&$a>2)return 1>>($a-2);else return bcmul(($p-1),bcpow($p,($a-1)));}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function order_mod($x,$m){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if($m<=1)return 0;if(gcd($x,m)==1){$z=$x;$result=1;while($z!=1){$z=gmp_strval(gmp_Utils::gmp_mod2(gmp_mul($z,$x),$m));$result=gmp_add($result,1);}return gmp_strval($result);}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if($m<=1)return 0;if(gcd($x,m)==1){$z=$x;$result=1;while($z!=1){$z=bcmod(bcmul($z,$x),$m);$result=bcadd($result,1);}return $result;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function largest_factor_relatively_prime($a,$b){if(extension_loaded('gmp')&&USE_EXT=='GMP'){while(true){$d=self::gcd($a,$b);if($d<=1)break;$b=$d;while(true){$q=$a/$d;$r=$a%$d;if($r>0)break;$a=$q;}}return $a;}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){while(true){$d=self::gcd($a,$b);if($d<=1)break;$b=$d;while(true){$q=$a/$d;$r=$a%$d;if($r>0)break;$a=$q;}}return $a;}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function kinda_order_mod($x,$m){if(extension_loaded('gmp')&&USE_EXT=='GMP'){return self::order_mod($x,self::largest_factor_relatively_prime($m,$x));}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){return self::order_mod($x,self::largest_factor_relatively_prime($m,$x));}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function is_prime($n){if(extension_loaded('gmp')&&USE_EXT=='GMP'){return gmp_prob_prime($n);}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){self::$miller_rabin_test_count=0;$t=40;$k=0;$m=bcsub($n,1);while(bcmod($m,2)==0){$k=bcadd($k,1);$m=bcdiv($m,2);}for($i=0;$i<$t;$i++){$a=bcmath_Utils::bcrand(1,bcsub($n,1));$b0=self::modular_exp($a,$m,$n);if($b0!=1&&$b0!=bcsub($n,1)){$j=1;while($j<=$k-1&&$b0!=bcsub($n,1)){$b0=self::modular_exp($b0,2,$n);if($b0==1){self::$miller_rabin_test_count=$i+1;return false;}$j++;}if($b0!=bcsub($n,1)){self::$miller_rabin_test_count=$i+1;return false;}}}return true;}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function next_prime($starting_value){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$result=gmp_strval(gmp_nextprime($starting_value));return $result;}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(bccomp($starting_value,2)==-1)return 2;$result=bcmath_Utils::bcor(bcadd($starting_value,1),1);while(!self::is_prime($result)){$result=bcadd($result,2);}return $result;}else{throw new ErrorException("Please install BCMATH or GMP");}}public static $miller_rabin_test_count;public static $smallprimes=array(2,3,5,7,11,13,17,19,23,29,31,37,41,43,47,53,59,61,67,71,73,79,83,89,97,101,103,107,109,113,127,131,137,139,149,151,157,163,167,173,179,181,191,193,197,199,211,223,227,229,233,239,241,251,257,263,269,271,277,281,283,293,307,311,313,317,331,337,347,349,353,359,367,373,379,383,389,397,401,409,419,421,431,433,439,443,449,457,461,463,467,479,487,491,499,503,509,521,523,541,547,557,563,569,571,577,587,593,599,601,607,613,617,619,631,641,643,647,653,659,661,673,677,683,691,701,709,719,727,733,739,743,751,757,761,769,773,787,797,809,811,821,823,827,829,839,853,857,859,863,877,881,883,887,907,911,919,929,937,941,947,953,967,971,977,983,991,997,1009,1013,1019,1021,1031,1033,1039,1049,1051,1061,1063,1069,1087,1091,1093,1097,1103,1109,1117,1123,1129,1151,1153,1163,1171,1181,1187,1193,1201,1213,1217,1223,1229);}
interface PointInterface{public function __construct(CurveFp$curve,$x,$y,$order=null);public static function cmp($p1,$p2);public static function add($p1,$p2);public static function mul($x2,Point$p1);public static function leftmost_bit($x);public static function rmul(Point$p1,$m);public function __toString();public static function double(Point$p1);public function getX();public function getY();public function getCurve();public function getOrder();}if(!defined('MAX_BASE'))define('MAX_BASE',128);
class Point implements PointInterface{public $curve;public $x;public $y;public $order;public static $infinity='infinity';public function __construct(CurveFp$curve,$x,$y,$order=null){$this->curve=$curve;$this->x=$x;$this->y=$y;$this->order=$order;if(isset($this->curve)&&($this->curve instanceof CurveFp)){if(!$this->curve->contains($this->x,$this->y)){throw new ErrorException("Curve".print_r($this->curve,true)." does not contain point ( ".$x." , ".$y." )");}if($this->order!=null){if(self::cmp(self::mul($order,$this),self::$infinity)!=0){throw new ErrorException("SELF * ORDER MUST EQUAL INFINITY.");}}}}public static function cmp($p1,$p2){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(!($p1 instanceof Point)){if(($p2 instanceof Point))return 1;if(!($p2 instanceof Point))return 0;}if(!($p2 instanceof Point)){if(($p1 instanceof Point))return 1;if(!($p1 instanceof Point))return 0;}if(gmp_cmp($p1->x,$p2->x)==0&&gmp_cmp($p1->y,$p2->y)==0&&CurveFp::cmp($p1->curve,$p2->curve)){return 0;}else{return 1;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(!($p1 instanceof Point)){if(($p2 instanceof Point))return 1;if(!($p2 instanceof Point))return 0;}if(!($p2 instanceof Point)){if(($p1 instanceof Point))return 1;if(!($p1 instanceof Point))return 0;}if(bccomp($p1->x,$p2->x)==0&&bccomp($p1->y,$p2->y)==0&&CurveFp::cmp($p1->curve,$p2->curve)){return 0;}else{return 1;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function add($p1,$p2){if(self::cmp($p2,self::$infinity)==0&&($p1 instanceof Point)){return $p1;}if(self::cmp($p1,self::$infinity)==0&&($p2 instanceof Point)){return $p2;}if(self::cmp($p1,self::$infinity)==0&&self::cmp($p2,self::$infinity)==0){return self::$infinity;}if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(CurveFp::cmp($p1->curve,$p2->curve)==0){if(gmp_Utils::gmp_mod2(gmp_cmp($p1->x,$p2->x),$p1->curve->getPrime())==0){if(gmp_Utils::gmp_mod2(gmp_add($p1->y,$p2->y),$p1->curve->getPrime())==0){return self::$infinity;}else{return self::double($p1);}}$p=$p1->curve->getPrime();$l=gmp_strval(gmp_mul(gmp_sub($p2->y,$p1->y),NumberTheory::inverse_mod(gmp_sub($p2->x,$p1->x),$p)));$x3=gmp_strval(gmp_Utils::gmp_mod2(gmp_sub(gmp_sub(gmp_pow($l,2),$p1->x),$p2->x),$p));$y3=gmp_strval(gmp_Utils::gmp_mod2(gmp_sub(gmp_mul($l,gmp_sub($p1->x,$x3)),$p1->y),$p));$p3=new Point($p1->curve,$x3,$y3);return $p3;}else{throw new ErrorException("The Elliptic Curves do not match.");}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(CurveFp::cmp($p1->curve,$p2->curve)==0){if(bcmod(bccomp($p1->x,$p2->x),$p1->curve->getPrime())==0){if(bcmod(bcadd($p1->y,$p2->y),$p1->curve->getPrime())==0){return self::$infinity;}else{return self::double($p1);}}$p=$p1->curve->getPrime();$l=bcmod(bcmul(bcsub($p2->y,$p1->y),NumberTheory::inverse_mod(bcsub($p2->x,$p1->x),$p)),$p);$x3=bcmod(bcsub(bcsub(bcpow($l,2),$p1->x),$p2->x),$p);$step0=bcsub($p1->x,$x3);$step1=bcmul($l,$step0);$step2=bcsub($step1,$p1->y);$step3=bcmod($step2,$p);$y3=bcmod(bcsub(bcmul($l,bcsub($p1->x,$x3)),$p1->y),$p);if(bccomp(0,$y3)==1)$y3=bcadd($p,$y3);$p3=new Point($p1->curve,$x3,$y3);return $p3;}else{throw new ErrorException("The Elliptic Curves do not match.");}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function mul($x2,Point$p1){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$e=$x2;if(self::cmp($p1,self::$infinity)==0){return self::$infinity;}if($p1->order!=null){$e=gmp_strval(gmp_Utils::gmp_mod2($e,$p1->order));}if(gmp_cmp($e,0)==0){return self::$infinity;}$e=gmp_strval($e);if(gmp_cmp($e,0)>0){$e3=gmp_mul(3,$e);$negative_self=new Point($p1->curve,$p1->x,gmp_strval(gmp_sub(0,$p1->y)),$p1->order);$i=gmp_div(self::leftmost_bit($e3),2);$result=$p1;while(gmp_cmp($i,1)>0){$result=self::double($result);if(gmp_cmp(gmp_and($e3,$i),0)!=0&&gmp_cmp(gmp_and($e,$i),0)==0){$result=self::add($result,$p1);}if(gmp_cmp(gmp_and($e3,$i),0)==0&&gmp_cmp(gmp_and($e,$i),0)!=0){$result=self::add($result,$negative_self);}$i=gmp_strval(gmp_div($i,2));}return $result;}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$e=$x2;if(self::cmp($p1,self::$infinity)==0){return self::$infinity;}if($p1->order!=null){$e=bcmod($e,$p1->order);}if(bccomp($e,0)==0){return self::$infinity;}if(bccomp($e,0)==1){$e3=bcmul(3,$e);$negative_self=new Point($p1->curve,$p1->x,bcsub(0,$p1->y),$p1->order);$i=bcdiv(self::leftmost_bit($e3),2);$result=$p1;while(bccomp($i,1)==1){$result=self::double($result);if(bccomp(bcmath_Utils::bcand($e3,$i),'0')!=0&&bccomp(bcmath_Utils::bcand($e,$i),'0')==0){$result=self::add($result,$p1);}if(bccomp(bcmath_Utils::bcand($e3,$i),0)==0&&bccomp(bcmath_Utils::bcand($e,$i),0)!=0){$result=self::add($result,$negative_self);}$i=bcdiv($i,2);}return $result;}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function leftmost_bit($x){if(extension_loaded('gmp')&&USE_EXT=='GMP'){if(gmp_cmp($x,0)>0){$result=1;while(gmp_cmp($result,$x)<0||gmp_cmp($result,$x)==0){$result=gmp_mul(2,$result);}return gmp_strval(gmp_div($result,2));}}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(bccomp($x,0)==1){$result=1;while(bccomp($result,$x)==-1||bccomp($result,$x)==0){$result=bcmul(2,$result);}return bcdiv($result,2);}}else{throw new ErrorException("Please install BCMATH or GMP");}}public static function rmul(Point$x1,$m){return self::mul($m,$x1);}public function __toString(){if(!($this instanceof Point)&&$this==self::$infinity)return self::$infinity;return"(".$this->x.",".$this->y.")";}public static function double(Point$p1){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$p=$p1->curve->getPrime();$a=$p1->curve->getA();$inverse=NumberTheory::inverse_mod(gmp_strval(gmp_mul(2,$p1->y)),$p);$three_x2=gmp_mul(3,gmp_pow($p1->x,2));$l=gmp_strval(gmp_Utils::gmp_mod2(gmp_mul(gmp_add($three_x2,$a),$inverse),$p));$x3=gmp_strval(gmp_Utils::gmp_mod2(gmp_sub(gmp_pow($l,2),gmp_mul(2,$p1->x)),$p));$y3=gmp_strval(gmp_Utils::gmp_mod2(gmp_sub(gmp_mul($l,gmp_sub($p1->x,$x3)),$p1->y),$p));if(gmp_cmp(0,$y3)>0)$y3=gmp_strval(gmp_add($p,$y3));$p3=new Point($p1->curve,$x3,$y3);return $p3;}elseif(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$p=$p1->curve->getPrime();$a=$p1->curve->getA();$inverse=NumberTheory::inverse_mod(bcmul(2,$p1->y),$p);$three_x2=bcmul(3,bcpow($p1->x,2));$l=bcmod(bcmul(bcadd($three_x2,$a),$inverse),$p);$x3=bcmod(bcsub(bcpow($l,2),bcmul(2,$p1->x)),$p);$y3=bcmod(bcsub(bcmul($l,bcsub($p1->x,$x3)),$p1->y),$p);if(bccomp(0,$y3)==1)$y3=bcadd($p,$y3);$p3=new Point($p1->curve,$x3,$y3);return $p3;}else{throw new ErrorException("Please install BCMATH or GMP");}}public function getX(){return $this->x;}public function getY(){return $this->y;}public function getCurve(){return $this->curve;}public function getOrder(){return $this->order;}}
class bcmath_Utils{public static function bcrand($min,$max=false){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){if(!$max){$max=$min;$min=0;}return bcadd(bcmul(bcdiv(mt_rand(0,mt_getrandmax()),mt_getrandmax(),strlen($max)),bcsub(bcadd($max,1),$min)),$min);}else{throw new ErrorException("Please install BCMATH");}}public static function bchexdec($hex){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$len=strlen($hex);$dec='';for($i=1;$i<=$len;$i++)$dec=bcadd($dec,bcmul(strval(hexdec($hex[$i-1])),bcpow('16',strval($len-$i))));return $dec;}else{throw new ErrorException("Please install BCMATH");}}public static function bcdechex($dec){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){$hex='';$positive=$dec<0?false:true;while($dec){$hex.=dechex(abs(bcmod($dec,'16')));$dec=bcdiv($dec,'16',0);}if($positive)return strrev($hex);for($i=0;$isset($hex[$i]);$i++)$hex[$i]=dechex(15-hexdec($hex[$i]));for($i=0;isset($hex[$i])&&$hex[$i]=='f';$i++)$hex[$i]='0';if(isset($hex[$i]))$hex[$i]=dechex(hexdec($hex[$i])+1);return strrev($hex);}else{throw new ErrorException("Please install BCMATH");}}public static function bcand($x,$y){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){return self::_bcbitwise_internal($x,$y,'bcmath_Utils::_bcand');}else{throw new ErrorException("Please install BCMATH");}}public static function bcor($x,$y){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){return self::_bcbitwise_internal($x,$y,'self::_bcor');}else{throw new ErrorException("Please install BCMATH");}}public static function bcxor($x,$y){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){return self::_bcbitwise_internal($x,$y,'self::_bcxor');}else{throw new ErrorException("Please install BCMATH");}}public static function bcleftshift($num,$shift){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){bcscale(0);return bcmul($num,bcpow(2,$shift));}else{throw new ErrorException("Please install BCMATH");}}public static function bcrightshift($num,$shift){if(extension_loaded('bcmath')&&USE_EXT=='BCMATH'){bcscale(0);return bcdiv($num,bcpow(2,$shift));}else{throw new ErrorException("Please install BCMATH");}}public static function _bcand($x,$y){return $x&$y;}public static function _bcor($x,$y){return $x|$y;}public static function _bcxor($x,$y){return $x^$y;}public static function _bcbitwise_internal($x,$y,$op){$bx=self::bc2bin($x);$by=self::bc2bin($y);self::equalbinpad($bx,$by);$ix=0;$ret='';for($ix=0;$ix<strlen($bx);$ix++){$xd=substr($bx,$ix,1);$yd=substr($by,$ix,1);$ret.=call_user_func($op,$xd,$yd);}return self::bin2bc($ret);}public static function bc2bin($num){return self::dec2base($num,MAX_BASE);}public static function bin2bc($num){return self::base2dec($num,MAX_BASE);}public static function dec2base($dec,$base,$digits=FALSE){if(extension_loaded('bcmath')){if($base<2||$base>256)die("Invalid Base: ".$base);bcscale(0);$value="";if(!$digits)$digits=self::digits($base);while($dec>$base-1){$rest=bcmod($dec,$base);$dec=bcdiv($dec,$base);$value=$digits[$rest].$value;}$value=$digits[intval($dec)].$value;return(string)$value;}else{throw new ErrorException("Please install BCMATH");}}public static function base2dec($value,$base,$digits=FALSE){if(extension_loaded('bcmath')){if($base<2||$base>256)die("Invalid Base: ".$base);bcscale(0);if($base<37)$value=strtolower($value);if(!$digits)$digits=self::digits($base);$size=strlen($value);$dec="0";for($loop=0;$loop<$size;$loop++){$element=strpos($digits,$value[$loop]);$power=bcpow($base,$size-$loop-1);$dec=bcadd($dec,bcmul($element,$power));}return(string)$dec;}else{throw new ErrorException("Please install BCMATH");}}public static function digits($base){if($base>64){$digits="";for($loop=0;$loop<256;$loop++){$digits.=chr($loop);}}else{$digits="0123456789abcdefghijklmnopqrstuvwxyz";$digits.="ABCDEFGHIJKLMNOPQRSTUVWXYZ-_";}$digits=substr($digits,0,$base);return(string)$digits;}public static function equalbinpad(&$x,&$y){$xlen=strlen($x);$ylen=strlen($y);$length=max($xlen,$ylen);self::fixedbinpad($x,$length);self::fixedbinpad($y,$length);}public static function fixedbinpad(&$num,$length){$pad='';for($ii=0;$ii<$length-strlen($num);$ii++){$pad.=self::bc2bin('0');}$num=$pad.$num;}}
class gmp_Utils {public static function gmp_mod2($n,$d){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$res=gmp_div_r($n,$d);if(gmp_cmp(0,$res)>0){$res=gmp_add($d,$res);}return gmp_strval($res);} else {throw new Exception("PLEASE INSTALL GMP");}}public static function gmp_random($n){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$random=gmp_strval(gmp_random());$small_rand=rand();while(gmp_cmp($random,$n)>0){$random=gmp_div($random,$small_rand,GMP_ROUND_ZERO);}return gmp_strval($random);}else{throw new Exception("PLEASE INSTALL GMP");}}public static function gmp_hexdec($hex){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$dec=gmp_strval(gmp_init($hex),10);return $dec;}else{throw new Exception("PLEASE INSTALL GMP");}}public static function gmp_dechex($dec){if(extension_loaded('gmp')&&USE_EXT=='GMP'){$hex=gmp_strval(gmp_init($dec),16);return $hex;}else{throw new Exception("PLEASE INSTALL GMP");}}}
interface PublicKeyInterface { public function __construct(Point $generator, Point $point); public function verifies($hash, Signature $signature); public function getCurve(); public function getGenerator(); public function getPoint(); }
interface PrivateKeyInterface { public function __construct(PublicKey $public_key, $secret_multiplier); public function sign($hash, $random_k); public static function int_to_string($x); public static function string_to_int($s); public static function digest_integer($m); public static function point_is_valid(Point $generator, $x, $y); }
class PublicKey implements PublicKeyInterface { protected $curve; protected $generator; protected $point; public function __construct(Point $generator, Point $point) { $this->curve = $generator->getCurve(); $this->generator = $generator; $this->point = $point; $n = $generator->getOrder(); if ($n == null) { throw new ErrorExcpetion("Generator Must have order."); } if (Point::cmp(Point::mul($n, $point), Point::$infinity) != 0) { throw new ErrorException("Generator Point order is bad."); } if (extension_loaded('gmp') && USE_EXT=='GMP') { if (gmp_cmp($point->getX(), 0) < 0 || gmp_cmp($n, $point->getX()) <= 0 || gmp_cmp($point->getY(), 0) < 0 || gmp_cmp($n, $point->getY()) <= 0) { throw new ErrorException("Generator Point has x and y out of range."); } } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') { if (bccomp($point->getX(), 0) == -1 || bccomp($n, $point->getX()) != 1 || bccomp($point->getY(), 0) == -1 || bccomp($n, $point->getY()) != 1) { throw new ErrorException("Generator Point has x and y out of range."); } } else { throw new ErrorException("Please install BCMATH or GMP"); } } public function verifies($hash, Signature $signature) { if (extension_loaded('gmp') && USE_EXT=='GMP') { $G = $this->generator; $n = $this->generator->getOrder(); $point = $this->point; $r = $signature->getR(); $s = $signature->getS(); if (gmp_cmp($r, 1) < 0 || gmp_cmp($r, gmp_sub($n, 1)) > 0) { return false; } if (gmp_cmp($s, 1) < 0 || gmp_cmp($s, gmp_sub($n, 1)) > 0) { return false; } $c = NumberTheory::inverse_mod($s, $n); $u1 = gmp_Utils::gmp_mod2(gmp_mul($hash, $c), $n); $u2 = gmp_Utils::gmp_mod2(gmp_mul($r, $c), $n); $xy = Point::add(Point::mul($u1, $G), Point::mul($u2, $point)); $v = gmp_Utils::gmp_mod2($xy->getX(), $n); if (gmp_cmp($v, $r) == 0) return true; else { return false; } } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') { $G = $this->generator; $n = $this->generator->getOrder(); $point = $this->point; $r = $signature->getR(); $s = $signature->getS(); if (bccomp($r, 1) == -1 || bccomp($r, bcsub($n, 1)) == 1) { return false; } if (bccomp($s, 1) == -1 || bccomp($s, bcsub($n, 1)) == 1) { return false; } $c = NumberTheory::inverse_mod($s, $n); $u1 = bcmod(bcmul($hash, $c), $n); $u2 = bcmod(bcmul($r, $c), $n); $xy = Point::add(Point::mul($u1, $G), Point::mul($u2, $point)); $v = bcmod($xy->getX(), $n); if (bccomp($v, $r) == 0) return true; else { return false; } } else { throw new ErrorException("Please install BCMATH or GMP"); } }  public function getCurve() { return $this->curve; } public function getGenerator() { return $this->generator; } public function getPoint() { return $this->point; } public function getPublicKey() { print_r($this); return $this; } }
class PrivateKey implements PrivateKeyInterface { private $public_key; private $secret_multiplier; public function __construct(PublicKey $public_key, $secret_multiplier) { $this->public_key = $public_key; $this->secret_multiplier = $secret_multiplier; } public function sign($hash, $random_k) { if (extension_loaded('gmp') && USE_EXT=='GMP') { $G = $this->public_key->getGenerator(); $n = $G->getOrder(); $k = gmp_Utils::gmp_mod2($random_k, $n); $p1 = Point::mul($k, $G); $r = $p1->getX(); if (gmp_cmp($r, 0) == 0) { throw new ErrorException("error: random number R = 0 <br />"); } $s = gmp_Utils::gmp_mod2(gmp_mul(NumberTheory::inverse_mod($k, $n), gmp_Utils::gmp_mod2(gmp_add($hash, gmp_mul($this->secret_multiplier, $r)), $n)), $n); if (gmp_cmp($s, 0) == 0) { throw new ErrorException("error: random number S = 0<br />"); } return new Signature($r, $s); } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') { $G = $this->public_key->getGenerator(); $n = $G->getOrder(); $k = bcmod($random_k, $n); $p1 = Point::mul($k, $G); $r = $p1->getX(); if (bccomp($r, 0) == 0) { throw new ErrorException("error: random number R = 0 <br />"); } $s = bcmod(bcmul(NumberTheory::inverse_mod($k, $n), bcmod(bcadd($hash, bcmul($this->secret_multiplier, $r)), $n)), $n); if (bccomp($s, 0) == 0) { throw new ErrorExcpetion("error: random number S = 0<br />"); } return new Signature($r, $s); } else { throw new ErrorException("Please install BCMATH or GMP"); } } public static function int_to_string($x) { if (extension_loaded('gmp') && USE_EXT=='GMP') { if (gmp_cmp($x, 0) >= 0) { if (gmp_cmp($x, 0) == 0) return chr(0); $result = ""; while (gmp_cmp($x, 0) > 0) { $q = gmp_div($x, 256, 0); $r = gmp_Utils::gmp_mod2($x, 256); $ascii = chr($r); $result = $ascii . $result; $x = $q; } return $result; } } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') { if (bccomp($x, 0) != -1) { if (bccomp($x, 0) == 0) return chr(0); $result = ""; while (bccomp($x, 0) == 1) { $q = bcdiv($x, 256, 0); $r = bcmod($x, 256); $ascii = chr($r); $result = $ascii . $result; $x = $q; } return $result; } } else { throw new ErrorException("Please install BCMATH or GMP"); } } public static function string_to_int($s) { if (extension_loaded('gmp') && USE_EXT=='GMP') { $result = 0; for ($c = 0; $c < strlen($s); $c++) { $result = gmp_add(gmp_mul(256, $result), ord($s[$c])); } return $result; } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') { $result = 0; for ($c = 0; $c < strlen($s); $c++) { $result = bcadd(bcmul(256, $result), ord($s[$c])); } return $result; } else { throw new ErrorException("Please install BCMATH or GMP"); } } public static function digest_integer($m) { return self::string_to_int(hash('sha1', self::int_to_string($m), true)); } public static function point_is_valid(Point $generator, $x, $y) { if (extension_loaded('gmp') && USE_EXT=='GMP') { $n = $generator->getOrder(); $curve = $generator->getCurve(); if (gmp_cmp($x, 0) < 0 || gmp_cmp($n, $x) <= 0 || gmp_cmp($y, 0) < 0 || gmp_cmp($n, $y) <= 0) { return false; } $containment = $curve->contains($x, $y); if (!$containment) { return false; } $point = new Point($curve, $x, $y); $op = Point::mul($n, $point); if (!(Point::cmp($op, Point::$infinity) == 0)) { return false; } return true; } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') { $n = $generator->getOrder(); $curve = $generator->getCurve(); if (bccomp($x, 0) == -1 || bccomp($n, $x) != 1 || bccomp($y, 0) == -1 || bccomp($n, $y) != 1) { return false; } $containment = $curve->contains($x, $y); if (!$containment) { return false; } $point = new Point($curve, $x, $y); $op = Point::mul($n, $point); if (!(Point::cmp($op, Point::$infinity) == 0)) { return false; } return true; } else { throw new ErrorException("Please install BCMATH or GMP"); } } } 
class NISTcurve { public static function curve_192() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '6277101735386680763835789423207666416083908700390324961279'; $_r = '6277101735386680763835789423176059013767194773182842284081'; $_b = gmp_Utils::gmp_hexdec('0x64210519e59c80e70fa7e9ab72243049feb8deecc146b9b1'); $_Gx = gmp_Utils::gmp_hexdec('0x188da80eb03090f67cbf20eb43a18800f4ff0afd82ff1012'); $_Gy = gmp_Utils::gmp_hexdec('0x07192b95ffc8da78631011ed6b24cdd573f977a11e794811'); $curve_192 = new CurveFp($_p, -3, $_b); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '6277101735386680763835789423207666416083908700390324961279'; $_r = '6277101735386680763835789423176059013767194773182842284081'; $_b = bcmath_Utils::bchexdec('0x64210519e59c80e70fa7e9ab72243049feb8deecc146b9b1'); $_Gx = bcmath_Utils::bchexdec('0x188da80eb03090f67cbf20eb43a18800f4ff0afd82ff1012'); $_Gy = bcmath_Utils::bchexdec('0x07192b95ffc8da78631011ed6b24cdd573f977a11e794811'); $curve_192 = new CurveFp($_p, -3, $_b); } return $curve_192; } public static function curve_224() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '26959946667150639794667015087019630673557916260026308143510066298881'; $_r = '26959946667150639794667015087019625940457807714424391721682722368061'; $_b = gmp_Utils::gmp_hexdec('0xb4050a850c04b3abf54132565044b0b7d7bfd8ba270b39432355ffb4'); $_Gx = gmp_Utils::gmp_hexdec('0xb70e0cbd6bb4bf7f321390b94a03c1d356c21122343280d6115c1d21'); $_Gy = gmp_Utils::gmp_hexdec('0xbd376388b5f723fb4c22dfe6cd4375a05a07476444d5819985007e34'); $curve_224 = new CurveFp($_p, -3, $_b); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '26959946667150639794667015087019630673557916260026308143510066298881'; $_r = '26959946667150639794667015087019625940457807714424391721682722368061'; $_b = bcmath_Utils::bchexdec('0xb4050a850c04b3abf54132565044b0b7d7bfd8ba270b39432355ffb4'); $_Gx = bcmath_Utils::bchexdec('0xb70e0cbd6bb4bf7f321390b94a03c1d356c21122343280d6115c1d21'); $_Gy = bcmath_Utils::bchexdec('0xbd376388b5f723fb4c22dfe6cd4375a05a07476444d5819985007e34'); $curve_224 = new CurveFp($_p, -3, $_b); } return $curve_224; } public static function curve_256() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '115792089210356248762697446949407573530086143415290314195533631308867097853951'; $_r = '115792089210356248762697446949407573529996955224135760342422259061068512044369'; $_b = gmp_Utils::gmp_hexdec('0x5ac635d8aa3a93e7b3ebbd55769886bc651d06b0cc53b0f63bce3c3e27d2604b'); $_Gx = gmp_Utils::gmp_hexdec('0x6b17d1f2e12c4247f8bce6e563a440f277037d812deb33a0f4a13945d898c296'); $_Gy = gmp_Utils::gmp_hexdec('0x4fe342e2fe1a7f9b8ee7eb4a7c0f9e162bce33576b315ececbb6406837bf51f5'); $curve_256 = new CurveFp($_p, -3, $_b); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '115792089210356248762697446949407573530086143415290314195533631308867097853951'; $_r = '115792089210356248762697446949407573529996955224135760342422259061068512044369'; $_b = bcmath_Utils::bchexdec('0x5ac635d8aa3a93e7b3ebbd55769886bc651d06b0cc53b0f63bce3c3e27d2604b'); $_Gx = bcmath_Utils::bchexdec('0x6b17d1f2e12c4247f8bce6e563a440f277037d812deb33a0f4a13945d898c296'); $_Gy = bcmath_Utils::bchexdec('0x4fe342e2fe1a7f9b8ee7eb4a7c0f9e162bce33576b315ececbb6406837bf51f5'); $curve_256 = new CurveFp($_p, -3, $_b); } return $curve_256; } public static function curve_384() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '39402006196394479212279040100143613805079739270465446667948293404245721771496870329047266088258938001861606973112319'; $_r = '39402006196394479212279040100143613805079739270465446667946905279627659399113263569398956308152294913554433653942643'; $_b = gmp_Utils::gmp_hexdec('0xb3312fa7e23ee7e4988e056be3f82d19181d9c6efe8141120314088f5013875ac656398d8a2ed19d2a85c8edd3ec2aef'); $_Gx = gmp_Utils::gmp_hexdec('0xaa87ca22be8b05378eb1c71ef320ad746e1d3b628ba79b9859f741e082542a385502f25dbf55296c3a545e3872760ab7'); $_Gy = gmp_Utils::gmp_hexdec('0x3617de4a96262c6f5d9e98bf9292dc29f8f41dbd289a147ce9da3113b5f0b8c00a60b1ce1d7e819d7a431d7c90ea0e5f'); $curve_384 = new CurveFp($_p, -3, $_b); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '39402006196394479212279040100143613805079739270465446667948293404245721771496870329047266088258938001861606973112319'; $_r = '39402006196394479212279040100143613805079739270465446667946905279627659399113263569398956308152294913554433653942643'; $_b = bcmath_Utils::bchexdec('0xb3312fa7e23ee7e4988e056be3f82d19181d9c6efe8141120314088f5013875ac656398d8a2ed19d2a85c8edd3ec2aef'); $_Gx = bcmath_Utils::bchexdec('0xaa87ca22be8b05378eb1c71ef320ad746e1d3b628ba79b9859f741e082542a385502f25dbf55296c3a545e3872760ab7'); $_Gy = bcmath_Utils::bchexdec('0x3617de4a96262c6f5d9e98bf9292dc29f8f41dbd289a147ce9da3113b5f0b8c00a60b1ce1d7e819d7a431d7c90ea0e5f'); $curve_384 = new CurveFp($_p, -3, $_b); } return $curve_384; } public static function curve_521() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '6864797660130609714981900799081393217269435300143305409394463459185543183397656052122559640661454554977296311391480858037121987999716643812574028291115057151'; $_r = '6864797660130609714981900799081393217269435300143305409394463459185543183397655394245057746333217197532963996371363321113864768612440380340372808892707005449'; $_b = gmp_Utils::gmp_hexdec('0x051953eb9618e1c9a1f929a21a0b68540eea2da725b99b315f3b8b489918ef109e156193951ec7e937b1652c0bd3bb1bf073573df883d2c34f1ef451fd46b503f00'); $_Gx = gmp_Utils::gmp_hexdec('0xc6858e06b70404e9cd9e3ecb662395b4429c648139053fb521f828af606b4d3dbaa14b5e77efe75928fe1dc127a2ffa8de3348b3c1856a429bf97e7e31c2e5bd66'); $_Gy = gmp_Utils::gmp_hexdec('0x11839296a789a3bc0045c8a5fb42c7d1bd998f54449579b446817afbd17273e662c97ee72995ef42640c550b9013fad0761353c7086a272c24088be94769fd16650'); $curve_521 = new CurveFp($_p, -3, $_b); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '6864797660130609714981900799081393217269435300143305409394463459185543183397656052122559640661454554977296311391480858037121987999716643812574028291115057151'; $_r = '6864797660130609714981900799081393217269435300143305409394463459185543183397655394245057746333217197532963996371363321113864768612440380340372808892707005449'; $_b = bcmath_Utils::bchexdec('0x051953eb9618e1c9a1f929a21a0b68540eea2da725b99b315f3b8b489918ef109e156193951ec7e937b1652c0bd3bb1bf073573df883d2c34f1ef451fd46b503f00'); $_Gx = bcmath_Utils::bchexdec('0xc6858e06b70404e9cd9e3ecb662395b4429c648139053fb521f828af606b4d3dbaa14b5e77efe75928fe1dc127a2ffa8de3348b3c1856a429bf97e7e31c2e5bd66'); $_Gy = bcmath_Utils::bchexdec('0x11839296a789a3bc0045c8a5fb42c7d1bd998f54449579b446817afbd17273e662c97ee72995ef42640c550b9013fad0761353c7086a272c24088be94769fd16650'); $curve_521 = new CurveFp($_p, -3, $_b); } return $curve_521; } public static function generator_192() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '6277101735386680763835789423207666416083908700390324961279'; $_r = '6277101735386680763835789423176059013767194773182842284081'; $_b = gmp_Utils::gmp_hexdec('0x64210519e59c80e70fa7e9ab72243049feb8deecc146b9b1'); $_Gx = gmp_Utils::gmp_hexdec('0x188da80eb03090f67cbf20eb43a18800f4ff0afd82ff1012'); $_Gy = gmp_Utils::gmp_hexdec('0x07192b95ffc8da78631011ed6b24cdd573f977a11e794811'); $curve_192 = new CurveFp($_p, -3, $_b); $generator_192 = new Point($curve_192, $_Gx, $_Gy, $_r); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '6277101735386680763835789423207666416083908700390324961279'; $_r = '6277101735386680763835789423176059013767194773182842284081'; $_b = bcmath_Utils::bchexdec('0x64210519e59c80e70fa7e9ab72243049feb8deecc146b9b1'); $_Gx = bcmath_Utils::bchexdec('0x188da80eb03090f67cbf20eb43a18800f4ff0afd82ff1012'); $_Gy = bcmath_Utils::bchexdec('0x07192b95ffc8da78631011ed6b24cdd573f977a11e794811'); $curve_192 = new CurveFp($_p, -3, $_b); $generator_192 = new Point($curve_192, $_Gx, $_Gy, $_r); } return $generator_192; } public static function generator_224() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '26959946667150639794667015087019630673557916260026308143510066298881'; $_r = '26959946667150639794667015087019625940457807714424391721682722368061'; $_b = gmp_Utils::gmp_hexdec('0xb4050a850c04b3abf54132565044b0b7d7bfd8ba270b39432355ffb4'); $_Gx = gmp_Utils::gmp_hexdec('0xb70e0cbd6bb4bf7f321390b94a03c1d356c21122343280d6115c1d21'); $_Gy = gmp_Utils::gmp_hexdec('0xbd376388b5f723fb4c22dfe6cd4375a05a07476444d5819985007e34'); $curve_224 = new CurveFp($_p, -3, $_b); $generator_224 = new Point($curve_224, $_Gx, $_Gy, $_r); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '26959946667150639794667015087019630673557916260026308143510066298881'; $_r = '26959946667150639794667015087019625940457807714424391721682722368061'; $_b = bcmath_Utils::bchexdec('0xb4050a850c04b3abf54132565044b0b7d7bfd8ba270b39432355ffb4'); $_Gx = bcmath_Utils::bchexdec('0xb70e0cbd6bb4bf7f321390b94a03c1d356c21122343280d6115c1d21'); $_Gy = bcmath_Utils::bchexdec('0xbd376388b5f723fb4c22dfe6cd4375a05a07476444d5819985007e34'); $curve_224 = new CurveFp($_p, -3, $_b); $generator_224 = new Point($curve_224, $_Gx, $_Gy, $_r); } return $generator_224; } public static function generator_256() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '115792089210356248762697446949407573530086143415290314195533631308867097853951'; $_r = '115792089210356248762697446949407573529996955224135760342422259061068512044369'; $_b = gmp_Utils::gmp_hexdec('0x5ac635d8aa3a93e7b3ebbd55769886bc651d06b0cc53b0f63bce3c3e27d2604b'); $_Gx = gmp_Utils::gmp_hexdec('0x6b17d1f2e12c4247f8bce6e563a440f277037d812deb33a0f4a13945d898c296'); $_Gy = gmp_Utils::gmp_hexdec('0x4fe342e2fe1a7f9b8ee7eb4a7c0f9e162bce33576b315ececbb6406837bf51f5'); $curve_256 = new CurveFp($_p, -3, $_b); $generator_256 = new Point($curve_256, $_Gx, $_Gy, $_r); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '115792089210356248762697446949407573530086143415290314195533631308867097853951'; $_r = '115792089210356248762697446949407573529996955224135760342422259061068512044369'; $_b = bcmath_Utils::bchexdec('0x5ac635d8aa3a93e7b3ebbd55769886bc651d06b0cc53b0f63bce3c3e27d2604b'); $_Gx = bcmath_Utils::bchexdec('0x6b17d1f2e12c4247f8bce6e563a440f277037d812deb33a0f4a13945d898c296'); $_Gy = bcmath_Utils::bchexdec('0x4fe342e2fe1a7f9b8ee7eb4a7c0f9e162bce33576b315ececbb6406837bf51f5'); $curve_256 = new CurveFp($_p, -3, $_b); $generator_256 = new Point($curve_256, $_Gx, $_Gy, $_r); } return $generator_256; } public static function generator_384() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '39402006196394479212279040100143613805079739270465446667948293404245721771496870329047266088258938001861606973112319'; $_r = '39402006196394479212279040100143613805079739270465446667946905279627659399113263569398956308152294913554433653942643'; $_b = gmp_Utils::gmp_hexdec('0xb3312fa7e23ee7e4988e056be3f82d19181d9c6efe8141120314088f5013875ac656398d8a2ed19d2a85c8edd3ec2aef'); $_Gx = gmp_Utils::gmp_hexdec('0xaa87ca22be8b05378eb1c71ef320ad746e1d3b628ba79b9859f741e082542a385502f25dbf55296c3a545e3872760ab7'); $_Gy = gmp_Utils::gmp_hexdec('0x3617de4a96262c6f5d9e98bf9292dc29f8f41dbd289a147ce9da3113b5f0b8c00a60b1ce1d7e819d7a431d7c90ea0e5f'); $curve_384 = new CurveFp($_p, -3, $_b); $generator_384 = new Point($curve_384, $_Gx, $_Gy, $_r); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '39402006196394479212279040100143613805079739270465446667948293404245721771496870329047266088258938001861606973112319'; $_r = '39402006196394479212279040100143613805079739270465446667946905279627659399113263569398956308152294913554433653942643'; $_b = bcmath_Utils::bchexdec('0xb3312fa7e23ee7e4988e056be3f82d19181d9c6efe8141120314088f5013875ac656398d8a2ed19d2a85c8edd3ec2aef'); $_Gx = bcmath_Utils::bchexdec('0xaa87ca22be8b05378eb1c71ef320ad746e1d3b628ba79b9859f741e082542a385502f25dbf55296c3a545e3872760ab7'); $_Gy = bcmath_Utils::bchexdec('0x3617de4a96262c6f5d9e98bf9292dc29f8f41dbd289a147ce9da3113b5f0b8c00a60b1ce1d7e819d7a431d7c90ea0e5f'); $curve_384 = new CurveFp($_p, -3, $_b); $generator_384 = new Point($curve_384, $_Gx, $_Gy, $_r); } return $generator_384; } public static function generator_521() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { $_p = '6864797660130609714981900799081393217269435300143305409394463459185543183397656052122559640661454554977296311391480858037121987999716643812574028291115057151'; $_r = '6864797660130609714981900799081393217269435300143305409394463459185543183397655394245057746333217197532963996371363321113864768612440380340372808892707005449'; $_b = gmp_Utils::gmp_hexdec('0x051953eb9618e1c9a1f929a21a0b68540eea2da725b99b315f3b8b489918ef109e156193951ec7e937b1652c0bd3bb1bf073573df883d2c34f1ef451fd46b503f00'); $_Gx = gmp_Utils::gmp_hexdec('0xc6858e06b70404e9cd9e3ecb662395b4429c648139053fb521f828af606b4d3dbaa14b5e77efe75928fe1dc127a2ffa8de3348b3c1856a429bf97e7e31c2e5bd66'); $_Gy = gmp_Utils::gmp_hexdec('0x11839296a789a3bc0045c8a5fb42c7d1bd998f54449579b446817afbd17273e662c97ee72995ef42640c550b9013fad0761353c7086a272c24088be94769fd16650'); $curve_521 = new CurveFp($_p, -3, $_b); $generator_521 = new Point($curve_521, $_Gx, $_Gy, $_r); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { $_p = '6864797660130609714981900799081393217269435300143305409394463459185543183397656052122559640661454554977296311391480858037121987999716643812574028291115057151'; $_r = '6864797660130609714981900799081393217269435300143305409394463459185543183397655394245057746333217197532963996371363321113864768612440380340372808892707005449'; $_b = bcmath_Utils::bchexdec('0x051953eb9618e1c9a1f929a21a0b68540eea2da725b99b315f3b8b489918ef109e156193951ec7e937b1652c0bd3bb1bf073573df883d2c34f1ef451fd46b503f00'); $_Gx = bcmath_Utils::bchexdec('0xc6858e06b70404e9cd9e3ecb662395b4429c648139053fb521f828af606b4d3dbaa14b5e77efe75928fe1dc127a2ffa8de3348b3c1856a429bf97e7e31c2e5bd66'); $_Gy = bcmath_Utils::bchexdec('0x11839296a789a3bc0045c8a5fb42c7d1bd998f54449579b446817afbd17273e662c97ee72995ef42640c550b9013fad0761353c7086a272c24088be94769fd16650'); $curve_521 = new CurveFp($_p, -3, $_b); $generator_521 = new Point($curve_521, $_Gx, $_Gy, $_r); } return $generator_521; } }
class Signature implements SignatureInterface{ protected $r; protected $s; public function  __construct($r, $s) { $this->r = $r; $this->s = $s; } public function getR(){ return $this->r; } public function getS(){ return $this->s; } }
interface SignatureInterface { public function __construct($r, $s); public function getR(); public function getS(); }
class PHPECC { private static function hex_encode($number) { $hex = gmp_strval(gmp_init($number, 10), 16); return (strlen($hex)%2 != 0) ? '0'.$hex : $hex; } private static function hex_decode($hex) { return gmp_strval(gmp_init($hex, 16), 10); } private static function bcmath_hex_private_key_to_hex_public_key($privatekey_hex) { $privatekey_dec = bcmath_Utils::bchexdec($privatekey_hex); $g = SECcurve::generator_secp256k1(); $point = Point::mul($privatekey_dec, $g); $publickey_bin = "\x04" . str_pad(bcmath_Utils::bc2bin($point->getX()), 32, "\x00", STR_PAD_LEFT) . str_pad(bcmath_Utils::bc2bin($point->getY()), 32, "\x00", STR_PAD_LEFT); $publickey_hex = bin2hex($publickey_bin); return $publickey_hex; } private static function gmp_hex_private_key_to_hex_public_key($privatekey_hex) { $privatekey_dec = self::hex_decode($privatekey_hex); $g = SECcurve::generator_secp256k1(); $point = Point::mul($privatekey_dec, $g); $xHex = self::hex_encode($point->getX()); $yHex = self::hex_encode($point->getY()); $xHex = str_pad($xHex, 64, '0', STR_PAD_LEFT); $yHex = str_pad($yHex, 64, '0', STR_PAD_LEFT); $publickey_hex = '04'.$xHex.$yHex; return $publickey_hex; } public static function hex_private_key_to_hex_public_key($privatekey_hex){ if (extension_loaded('gmp') && USE_EXT == 'GMP') { return self::gmp_hex_private_key_to_hex_public_key($privatekey_hex); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return self::bcmath_hex_private_key_to_hex_public_key($privatekey_hex); } } public static function bcmath_hex_private_key_genorate() { for ($i = 0; $i < 32; $i++) { $privatekey_bin .= chr(mt_rand(0, $i ? 0xff : 0xfe)); } return $privatekey_hex = bin2hex($privatekey_bin); } public static function gmp_hex_private_key_genorate() { $g = SECcurve::generator_secp256k1(); $n = $g->getOrder(); $privatekey_dec = gmp_strval(gmp_init(bin2hex(openssl_random_pseudo_bytes(32)),16)); while($privatekey_dec >= $n) { $privatekey_dec = gmp_strval(gmp_init(bin2hex(openssl_random_pseudo_bytes(32)),16)); } $privatekey_hex = self::hex_encode($privatekey_dec); return $privatekey_hex = str_pad($privatekey_hex, 64, '0', STR_PAD_LEFT); } public static function hex_private_key_genorate() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return self::gmp_hex_private_key_genorate(); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return self::bcmath_hex_private_key_genorate(); } } public static function hex_keypair_genorate() { $keypair = array('private' => '', 'public' => ''); $keypair['private'] = self::hex_private_key_genorate(); $keypair['public'] = self::hex_private_key_to_hex_public_key($keypair['private']); return $keypair; } }
class SECcurve { private static function secp128r1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array('p' => gmp_Utils::gmp_hexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFF'), 'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFC'), 'b' => gmp_Utils::gmp_hexdec('0xE87579C11079F43DD824993C2CEE5ED3'), 'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFE0000000075A30D1B9038A115'), 'x' => gmp_Utils::gmp_hexdec("0x161FF7528B899B2D0C28607CA52C5B86"), 'y' => gmp_Utils::gmp_hexdec("0xCF5AC8395BAFEB13C02DA292DDED7A83") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array('p' => bcmath_Utils::bchexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFF'), 'a' => bcmath_Utils::bchexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFC'), 'b' => bcmath_Utils::bchexdec('0xE87579C11079F43DD824993C2CEE5ED3'), 'n' => bcmath_Utils::bchexdec('0xFFFFFFFE0000000075A30D1B9038A115'), 'x' => bcmath_Utils::bchexdec("0x161FF7528B899B2D0C28607CA52C5B86"), 'y' => bcmath_Utils::bchexdec("0xCF5AC8395BAFEB13C02DA292DDED7A83") ); } } private static function secp160k1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array( 'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFAC73'), 'a' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000000'), 'b' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000007'), 'n' => gmp_Utils::gmp_hexdec('0x0100000000000000000001B8FA16DFAB9ACA16B6B3'), 'x' => gmp_Utils::gmp_hexdec("0x3B4C382CE37AA192A4019E763036F4F5DD4D7EBB"), 'y' => gmp_Utils::gmp_hexdec("0x938CF935318FDCED6BC28286531733C3F03C4FEE") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array( 'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFAC73'), 'a' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000000'), 'b' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000007'), 'n' => bcmath_Utils::bchexdec('0x0100000000000000000001B8FA16DFAB9ACA16B6B3'), 'x' => bcmath_Utils::bchexdec("0x3B4C382CE37AA192A4019E763036F4F5DD4D7EBB"), 'y' => bcmath_Utils::bchexdec("0x938CF935318FDCED6BC28286531733C3F03C4FEE") ); } } private static function secp160r1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array('p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFF'), 'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFC'), 'b' => gmp_Utils::gmp_hexdec('0x1C97BEFC54BD7A8B65ACF89F81D4D4ADC565FA45'), 'n' => gmp_Utils::gmp_hexdec('0x0100000000000000000001F4C8F927AED3CA752257'), 'x' => gmp_Utils::gmp_hexdec("0x4A96B5688EF573284664698968C38BB913CBFC82"), 'y' => gmp_Utils::gmp_hexdec("0x23A628553168947D59DCC912042351377AC5FB32") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array( 'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFF'), 'a' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFC'), 'b' => bcmath_Utils::bchexdec('0x1C97BEFC54BD7A8B65ACF89F81D4D4ADC565FA45'), 'n' => bcmath_Utils::bchexdec('0x0100000000000000000001F4C8F927AED3CA752257'), 'x' => bcmath_Utils::bchexdec("0x4A96B5688EF573284664698968C38BB913CBFC82"), 'y' => bcmath_Utils::bchexdec("0x23A628553168947D59DCC912042351377AC5FB32") ); } } private static function secp192k1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array('p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFEE37'), 'a' => gmp_Utils::gmp_hexdec('0x000000000000000000000000000000000000000000000000'), 'b' => gmp_Utils::gmp_hexdec('0x000000000000000000000000000000000000000000000003'), 'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFE26F2FC170F69466A74DEFD8D'), 'x' => gmp_Utils::gmp_hexdec("0xDB4FF10EC057E9AE26B07D0280B7F4341DA5D1B1EAE06C7D"), 'y' => gmp_Utils::gmp_hexdec("0x9B2F2F6D9C5628A7844163D015BE86344082AA88D95E2F9D") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array('p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFEE37'), 'a' => bcmath_Utils::bchexdec('0x000000000000000000000000000000000000000000000000'), 'b' => bcmath_Utils::bchexdec('0x000000000000000000000000000000000000000000000003'), 'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFE26F2FC170F69466A74DEFD8D'), 'x' => bcmath_Utils::bchexdec("0xDB4FF10EC057E9AE26B07D0280B7F4341DA5D1B1EAE06C7D"), 'y' => bcmath_Utils::bchexdec("0x9B2F2F6D9C5628A7844163D015BE86344082AA88D95E2F9D") ); } } private static function secp192r1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array('p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFF'), 'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFC'), 'b' => gmp_Utils::gmp_hexdec('0x64210519E59C80E70FA7E9AB72243049FEB8DEECC146B9B1'), 'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFF99DEF836146BC9B1B4D22831'), 'x' => gmp_Utils::gmp_hexdec("0x188DA80EB03090F67CBF20EB43A18800F4FF0AFD82FF1012"), 'y' => gmp_Utils::gmp_hexdec("0x07192B95FFC8DA78631011ED6B24CDD573F977A11E794811") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array( 'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFF'), 'a' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFC'), 'b' => bcmath_Utils::bchexdec('0x64210519E59C80E70FA7E9AB72243049FEB8DEECC146B9B1'), 'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFF99DEF836146BC9B1B4D22831'), 'x' => bcmath_Utils::bchexdec("0x188DA80EB03090F67CBF20EB43A18800F4FF0AFD82FF1012"), 'y' => bcmath_Utils::bchexdec("0x07192B95FFC8DA78631011ED6B24CDD573F977A11E794811") ); } } private static function secp224r1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array('p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF000000000000000000000001'), 'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFE'), 'b' => gmp_Utils::gmp_hexdec('0xB4050A850C04B3ABF54132565044B0B7D7BFD8BA270B39432355FFB4'), 'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFF16A2E0B8F03E13DD29455C5C2A3D'), 'x' => gmp_Utils::gmp_hexdec("0xB70E0CBD6BB4BF7F321390B94A03C1D356C21122343280D6115C1D21"), 'y' => gmp_Utils::gmp_hexdec("0xBD376388B5F723FB4C22DFE6CD4375A05A07476444D5819985007E34") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array( 'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF000000000000000000000001'), 'a' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFE'), 'b' => bcmath_Utils::bchexdec('0xB4050A850C04B3ABF54132565044B0B7D7BFD8BA270B39432355FFB4'), 'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFF16A2E0B8F03E13DD29455C5C2A3D'), 'x' => bcmath_Utils::bchexdec("0xB70E0CBD6BB4BF7F321390B94A03C1D356C21122343280D6115C1D21"), 'y' => bcmath_Utils::bchexdec("0xBD376388B5F723FB4C22DFE6CD4375A05A07476444D5819985007E34") ); } } private static function secp256r1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array('p' => gmp_Utils::gmp_hexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFF'), 'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFC'), 'b' => gmp_Utils::gmp_hexdec('0x5AC635D8AA3A93E7B3EBBD55769886BC651D06B0CC53B0F63BCE3C3E27D2604B'), 'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFF00000000FFFFFFFFFFFFFFFFBCE6FAADA7179E84F3B9CAC2FC632551'), 'x' => gmp_Utils::gmp_hexdec("0x6B17D1F2E12C4247F8BCE6E563A440F277037D812DEB33A0F4A13945D898C296"), 'y' => gmp_Utils::gmp_hexdec("0x4FE342E2FE1A7F9B8EE7EB4A7C0F9E162BCE33576B315ECECBB6406837BF51F5") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array( 'p' => bcmath_Utils::bchexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFF'), 'a' => bcmath_Utils::bchexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFC'), 'b' => bcmath_Utils::bchexdec('0x5AC635D8AA3A93E7B3EBBD55769886BC651D06B0CC53B0F63BCE3C3E27D2604B'), 'n' => bcmath_Utils::bchexdec('0xFFFFFFFF00000000FFFFFFFFFFFFFFFFBCE6FAADA7179E84F3B9CAC2FC632551'), 'x' => bcmath_Utils::bchexdec("0x6B17D1F2E12C4247F8BCE6E563A440F277037D812DEB33A0F4A13945D898C296"), 'y' => bcmath_Utils::bchexdec("0x4FE342E2FE1A7F9B8EE7EB4A7C0F9E162BCE33576B315ECECBB6406837BF51F5") ); } } private static function secp256k1_params() { if (extension_loaded('gmp') && USE_EXT == 'GMP') { return array('p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F'), 'a' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000000000000000000000000000000'), 'b' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000000000000000000000000000007'), 'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141'), 'x' => gmp_Utils::gmp_hexdec("0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798"), 'y' => gmp_Utils::gmp_hexdec("0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8") ); } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') { return array( 'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F'), 'a' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000000000000000000000000000000'), 'b' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000000000000000000000000000007'), 'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141'), 'x' => bcmath_Utils::bchexdec("0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798"), 'y' => bcmath_Utils::bchexdec("0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8") ); } }  public static function curve_secp128r1() { $c_params = self::secp128r1_params(); $curve_secp128r1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp128r1; } public static function generator_secp128r1() { $c_params = self::secp128r1_params(); $generator_secp128r1 = new Point(self::curve_secp128r1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp128r1; } public static function curve_secp160k1() { $c_params = self::secp160k1_params(); $curve_secp160k1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp160k1; } public static function generator_secp160k1() { $c_params = self::secp160k1_params(); $generator_secp160k1 = new Point(self::curve_secp160k1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp160k1; } public static function curve_secp160r1() { $c_params = self::secp160r1_params(); $curve_secp160r1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp160r1; } public static function generator_secp160r1() { $c_params = self::secp160r1_params(); $generator_secp160r1 = new Point(self::curve_secp160r1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp160r1; } public static function curve_secp192k1() { $c_params = self::secp192k1_params(); $curve_secp192k1= new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp192k1; } public static function generator_secp192k1() { $c_params = self::secp192k1_params(); $generator_secp192k1 = new Point(self::curve_secp192k1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp192k1; } public static function curve_secp192r1() { $c_params = self::secp192r1_params(); $curve_secp192r1= new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp192r1; } public static function generator_secp192r1() { $c_params = self::secp192r1_params(); $generator_secp192r1 = new Point(self::curve_secp192r1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp192r1; } public static function curve_secp224r1() { $c_params = self::secp224r1_params(); $curve_secp224r1= new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp224r1; } public static function generator_secp224r1() { $c_params = self::secp224r1_params(); $generator_secp224r1 = new Point(self::curve_secp224r1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp224r1; } public static function curve_secp256r1() { $c_params = self::secp256r1_params(); $curve_secp256r1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp256r1; } public static function generator_secp256r1() { $c_params = self::secp256r1_params(); $generator_secp256r1 = new Point(self::curve_secp256r1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp256r1; } public static function curve_secp256k1() { $c_params = self::secp256k1_params(); $curve_secp256k1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']); return $curve_secp256k1; } public static function generator_secp256k1() { $c_params = self::secp256k1_params(); $generator_secp256k1 = new Point(self::curve_secp256k1(), $c_params['x'], $c_params['y'], $c_params['n']); return $generator_secp256k1; } }
// END: PHP ECC Libs - Compacted

class BIP32 {

////////////////////////////////////////////////////////////////////////////
// Construct
////////////////////////////////////////////////////////////////////////////

public function __construct() { 
	$this->public_prefix = TESTNET == 1 ? '6F' : '00';
	$this->private_prefix = TESTNET == 1 ? 'EF' : '80';
}

////////////////////////////////////////////////////////////////////////////
// Generate master key
////////////////////////////////////////////////////////////////////////////

public function generate_master_key($seed = '') { 

	// Generate seed, if needed
	if (empty($seed)) { 
		for ($i = 0; $i < 1024; $i++) { $seed .= chr(mt_rand(0, $i ? 0xff : 0xfe)); }
		$seed = bin2hex($seed);
	}

	// Encrypt
	$I = hash_hmac('sha512', pack("H*", $seed), generate_random_string(1024));
	$I_l = substr($I, 0, 64);
	$I_r = substr($I, 64, 64);

	// Set results
	$results = array(
		'network' => 'bitcoin', 
		'testnet' => TESTNET == 1 ? true : false, 
		'type' => 'private', 
		'depth' => 0, 
		'fingerprint' => '00000000', 
		'i' => '00000000', 
		'chain_code' => $I_r, 
		'key' => $I_l
	);
	
	// Return
	return $this->build_key($this->encode($results), '0/0')[0];

}

////////////////////////////////////////////////////////////////////
// Add new wallet
////////////////////////////////////////////////////////////////////

public function add_wallet() { 

	// Initialize
	global $template;
	$enc_client = new encrypt();

	// Set variables
	$required_sigs = $_POST['address_type'] == 'standard' ? 1 : $_POST['multisig_sig_required'];
	$total_sigs = $_POST['address_type'] == 'standard' ? 1 : $_POST['multisig_sig_total'];

	// Validate public keys
	if ($_POST['autogen_keys'] == 0) { 
		for ($x=1; $x <= $total_sigs; $x++) { 
			if (!$import = $this->import($_POST['bip32_key' . $x])) { $template->add_message("The #$x BIP32 key you specified is an invalid BIP32 key.", 'error'); }
			elseif ($import['type'] != 'public') { $template->add_message("The #$x BIP32 key you specified is an invalid BIP32 key.", 'error'); }
		}
	}

	// Create wallet, if no errors
	$wallet_id = 0;
	if ($template->has_errors != 1) { 

		// Add to DB
		DB::insert('coin_wallets', array(
			'address_type' => $_POST['address_type'], 
			'sigs_required' => $required_sigs, 
			'sigs_total' => $total_sigs, 
			'display_name' => $_POST['wallet_name'])
		);
		$wallet_id = DB::insertId();

		// Gather BIP32 keys
		$keys = array();
		for ($x=1; $x <= $total_sigs; $x++) { 

			// Auto-generate, if needed
			if ($_POST['autogen_keys'] == 1) { 
				$private_key = $this->generate_master_key();
				$public_key = $this->extended_private_to_public($private_key);

				array_push($keys, array(
					'num' => $x, 
					'private_key' => $private_key, 
					'public_key' => $public_key)
				);

			} else { $public_key = $_POST['bip32_key' . $x]; }

			// Add key to db
			DB::insert('coin_wallets_keys', array(
				'wallet_id' => $wallet_id, 
				'public_key' => $enc_client->encrypt($public_key))
			);
		}

		// User message
		if ($_POST['autogen_keys'] == 1) { 
			$template = new template('admin/setup/bip32_keys');
			$template->assign('keys', $keys);
			$template->parse(); exit(0);

		} else { 
			$template->add_message("Successfully added new wallet, $_POST[wallet_name]");
		}
	}

	// Return
	return $wallet_id;

}

////////////////////////////////////////////////////////////////////
// Get wallet balance
////////////////////////////////////////////////////////////////////

public function get_balance($wallet_id) { 

	// Get balance
	$balance = DB::queryFirstField("SELECT sum(amount) FROM coin_inputs WHERE wallet_id = %d AND is_spent = 0", $wallet_id);
	if ($balance == '') { $balance = 0; }

	// Withdraw pending sends
	$pending_sends = DB::queryFirstField("SELECT sum(amount) FROM coin_sends WHERE wallet_id = %d AND status = 'pending'", $wallet_id);
	$balance -= $pending_sends;

	// Return
	return fmoney_coin($balance);

}

////////////////////////////////////////////////////////////////////
// Generate address
////////////////////////////////////////////////////////////////////

public function generate_address($wallet_id, $userid = 0, $is_change_address = 0) { 

	// Initialize
	$enc_client = new encrypt();

	// Get wallet
	if (!$wrow = DB::queryFirstRow("SELECT * FROM coin_wallets WHERE id = %d", $wallet_id)) { 
		trigger_error("Wallet does not exist, ID# $wallet_id", E_USER_ERROR);
	}

	// Go through keys
	$child_keys = array(); $public_keys = array();
	$rows = DB::query("SELECT * FROM coin_wallets_keys WHERE wallet_id = %d ORDER BY id", $wallet_id);
	foreach ($rows as $row) { 

		// Get public key & key index
		$public_key = trim($enc_client->decrypt(trim($row['public_key'])));
		$keyindex = $is_change_address . '/' . $row['address_num'];
		DB::query("UPDATE coin_wallets_keys SET address_num = address_num + 1 WHERE id = %d", $row['id']);

		// Generate child key
		$child_ext_key = $this->build_key($public_key, $keyindex)[0];
		$child_key = $this->import($child_ext_key)['key'];

		// Return standard address, if needed
		if ($wrow['address_type'] == 'standard') { 
			$address = $this->key_to_address($child_ext_key);
			$this->add_address($wallet_id, $address, $userid, $is_change_address, $row['address_num'], $row['id']);
			return $address;
		}

		// Add to arrays
		$public_keys[] = $child_key;
		$child_keys[] = array(
			'address' => $this->key_to_address($child_ext_key), 
			'key_id' => $row['id'], 
			'public_key' => $child_key, 
			'address_num' => $row['address_num']
		);
	}

	// Create redeem script
	$redeem_script = $this->create_redeem_script($wrow['sigs_required'], $public_keys);

	// Generate address
	$hash160 = (TESTNET == 1 ? 'c4' : '05') . hash('ripemd160', hash('sha256', pack('H*', $redeem_script), true));
	$address = $this->base58_encode_checksum($hash160);

	// Add address
	$this->add_address($wallet_id, $address, $userid, $is_change_address);
	foreach ($child_keys as $vars) { 
		DB::insert('coin_addresses_multisig', array(
			'key_id' => $vars['key_id'], 
			'address' => $address, 
			'address_num' => $vars['address_num'], 
			'child_address' => $vars['address'])
		);
	}

	// Return
	return $address;

}

////////////////////////////////////////////////////////////////////
// Add address
////////////////////////////////////////////////////////////////////

public function add_address($wallet_id, $address, $userid = 0, $is_change_address = 0, $address_num = 0, $key_id = 0) { 

	// Initialize
	global $config;
	
	// Add address to db
	DB::insert('coin_addresses', array(
		'wallet_id' => $wallet_id, 
		'key_id' => $key_id, 
		'userid' => $userid, 
		'is_change_address' => $is_change_address, 
		'address_num' => $address_num, 
		'address' => $address)
	);

	// Init RPC client
	include_once(SITE_PATH . '/data/lib/jsonRPCClient.php');
	$rpc_url = 'http://' . $config['btc_rpc_user'] . ':' . $config['btc_rpc_pass'] . '@' . $config['btc_rpc_host'] . ':' . $config['btc_rpc_port'];
	$client = new jsonRPCClient($rpc_url);

	// Import address to bitcoind
	try {
		$client->importaddress($address, "", false);
	} catch (Exception $e) { 
		trigger_error("Unable to import address into Bitcoin Core as a watch only address, $address.  Please ensure Bitcoin Core is running, and in the correct mode (mainnet / testnet).", E_USER_ERROR);
	}

}

////////////////////////////////////////////////////////////////////
// Get user address
////////////////////////////////////////////////////////////////////

public function get_user_address($wallet_id, $userid = 0) { 

	// Get address
	if ($row = DB::queryFirstRow("SELECT * FROM coin_addresses WHERE wallet_id = %d AND userid = %d AND is_used = 0", $wallet_id, $userid)) { 
		return $row['address'];
	}

	// Generate new address
	$address = $this->generate_address($wallet_id, $userid);

	// Return
	return $address;

}

////////////////////////////////////////////////////////////////////
// Extended private to public key
////////////////////////////////////////////////////////////////////

public function extended_private_to_public($input) {

	// Check input
	if (is_array($input) && count($input) == 2) { 
		$ext_private_key = $input[0];
		$generated = $input[1];
	} else if (is_string($input) === true) { 
		$ext_private_key = $input;
		$generated = false;
	} else { return false; }

	// Import key
	$pubkey = $this->import($ext_private_key);
	if ($pubkey['type'] != 'private') { return false; }
	
	// Decode
	$pubkey['key'] = $this->private_to_public($pubkey['key'], true);
	$pubkey['type'] = 'public';

	// Return
	if ($generated !== false) { 
		$generated = str_replace('m', 'M', $generated);
		return array($this->encode($pubkey), $generated);
	} else { 
		return $this->encode($pubkey);
	}

}

////////////////////////////////////////////////////////////////////
// Private to public key
////////////////////////////////////////////////////////////////////

public function private_to_public($privkey, $compressed = false) { 

	// Decode private key
	$g = SECcurve::generator_secp256k1();
	$privkey = $this->hex_decode($privkey);  

	try {
		$secretG = Point::mul($privkey, $g);
	} catch (Exception $e) { return false; }
	
	// Get points
	$xHex = $this->hex_encode($secretG->getX());  
	$yHex = $this->hex_encode($secretG->getY());
	$xHex = str_pad($xHex, 64, '0', STR_PAD_LEFT);
	$yHex = str_pad($yHex, 64, '0', STR_PAD_LEFT);

	// Set new key
	$key = '04' . $xHex . $yHex;
	if ($compressed === true) { 
		$key = '0' . (((gmp_Utils::gmp_mod2(gmp_init(substr($key, 66, 64), 16), 2))==0) ? '2' : '3') . substr($key, 2, 64);
	}
	
	// Return
	return $key;

}

////////////////////////////////////////////////////////////////////////////
// Private key to WIF
////////////////////////////////////////////////////////////////////////////

public function private_to_wif($privkey, $compressed = false) { 

	// Import key
	$import = $this->import($privkey);
	$address_version = gmp_strval(gmp_add(gmp_init($import['version'], 16), gmp_init('80',16)), 16);	
	$key = $import['key'] . (($compressed === true) ? '01' : '');

	// Return
	return $this->base58_encode_checksum($address_version . $key);

}

////////////////////////////////////////////////////////////////////////////
// WIF to private key
////////////////////////////////////////////////////////////////////////////

public function wif_to_private($wif) { 
	$decode = $this->base58_decode($wif);
	return array(
		'key' => substr($decode, 2, 64), 
		'is_compressed' => (( (strlen($decode)-10) == 66 && substr($decode, 66, 2) == '01') ? TRUE : FALSE)
	);

}

////////////////////////////////////////////////////////////////////////////
// Build new key
////////////////////////////////////////////////////////////////////////////

public function build_key($input, $string_def) { 

	// Check input
	if (is_array($input) && count($input) == 2) { 
		$parent = $input[0];
		$def = $input[1];
	} else if (is_string($input) === true) { 
		$parent = $input;
	} else { return false; }

	// Get address definition
	$address_definition = $this->get_definition_tuple($parent, $string_def);

	// Generate new key
	if (isset($def) === true) {
		$extended_key = $this->CKD($parent, $address_definition, explode("/", $def));
	} else {
		$extended_key = $this->CKD($parent, $address_definition);
	}
	
	// Return
	return $extended_key;
	
}

////////////////////////////////////////////////////////////////////////////
// Key to address
////////////////////////////////////////////////////////////////////////////

public function key_to_address($extended_key) { 

	// Import key
	$import = $this->import($extended_key);

	// Get public key	
	if ($import['type'] == 'public') {
		$public = $import['key'];
	} else if($import['type'] == 'private') {
		$public = $this->private_to_public($import['key'], true);
	} else { return false; }
	
	// Generate address
	$hash160 = $import['version'] . hash('ripemd160', hash('sha256', pack("H*", $public), true));
	return $this->base58_encode_checksum($hash160);

}

////////////////////////////////////////////////////////////////////////////
// CKD
////////////////////////////////////////////////////////////////////////////

public function CKD($master, $address_definition, $generated = array()) { 

	// Import master
	$previous = $this->import($master);

	// Check key type
	if ($previous['type'] == 'private') {
		$private_key = $previous['key'];
		$public_key = $this->private_to_public($private_key, true);
	} else if($previous['type'] == 'public') { 
		$public_key = $previous['key'];
	} else { return false; }

	// Get fingerprint
	$fingerprint = substr(hash('ripemd160', hash('sha256', pack("H*", $public_key), true)), 0, 8);
	$i = array_pop($address_definition);
		
	// Check prime
	$is_prime = (	gmp_cmp(gmp_init($i, 16), gmp_init('80000000', 16)) == -1 ) ? 0 : 1;
	if ($is_prime == 1) {
		if ($previous['type'] == 'public') { return false; }
		$data = '00' . $private_key . $i;
	} else if ($is_prime == 0) { $data = $public_key . $i; }

	// Hash data
	if (!isset($data)) { return false; }	
	$I = hash_hmac('sha512', pack("H*", $data), pack("H*", $previous['chain_code']));
	$I_l = substr($I, 0, 64);
	$I_r = substr($I, 64, 64);
		
	// Initialize curve
	$g = SECcurve::generator_secp256k1();
	$n = $g->getOrder();

	// Generate key		
	if ($previous['type'] == 'private') {
		$key = str_pad(gmp_strval(gmp_Utils::gmp_mod2(gmp_add(gmp_init($I_l, 16), gmp_init($private_key, 16)), $n), 16), 64, '0', STR_PAD_LEFT);
	} else if ($previous['type'] == 'public') {
		$decompressed = $this->decompress_public_key($public_key);
		$curve = SECcurve::curve_secp256k1();
		$new_point = Point::add(Point::mul(gmp_init($I_l, 16), $g), $decompressed['point']);

		$new_x = str_pad(gmp_strval($new_point->getX(), 16), 64, '0', STR_PAD_LEFT);
		$new_y = str_pad(gmp_strval($new_point->getY(), 16), 64, '0', STR_PAD_LEFT);

		$key = '04' . $new_x . $new_y;
		$key = '0' . (((gmp_Utils::gmp_mod2(gmp_init(substr($key, 66, 64), 16), 2)) == 0) ? '2' : '3') . substr($key, 2, 64);
		//$key = preg_replace("/^04/", "", $key);
	}		
	if(!isset($key)) return FALSE;

	// Set data
	$data = array(
		'network' => $previous['network'],
		'testnet' => $previous['testnet'],
		'magic_bytes' => $previous['magic_bytes'],
		'type' => $previous['type'],
		'depth' => $previous['depth'] + 1,
		'fingerprint' => $fingerprint,
		'i' => $i, 
		'address_number' => $this->get_address_number($i),
		'chain_code' => $I_r,
		'key' => $key
	);
	
	// Return
	if (count($address_definition) > 0) { 
		return $this->CKD($this->encode($data), $address_definition, $generated);
	} else { 
		return array($this->encode($data), implode('/', $generated));
	}

}

////////////////////////////////////////////////////////////////////////////
// Create redeem script
////////////////////////////////////////////////////////////////////////////

public function create_redeem_script($m, $public_keys) { 
	if (count($public_keys) == 0) { return FALSE; }
	if ($m == 0) { return FALSE; }
	
	$redeemScript = dechex(0x50+$m);
	foreach($public_keys as $public_key) {
		$redeemScript .= dechex(strlen($public_key)/2).$public_key;
	}
	$redeemScript .= dechex(0x50+(count($public_keys))) . 'ae';
	
	// Return
	return $redeemScript;

}

////////////////////////////////////////////////////////////////////////////
// Get sigscript from address
////////////////////////////////////////////////////////////////////////////

public function address_to_sigscript($address) { 

	// Initialize
	$enc = new encrypt();

	// Get address
	if (!$addr_row = DB::queryFirstRow("SELECT * FROM coin_addresses WHERE address = %s", $address)) { 
		trigger_error("Address does not exist, $address", E_USER_ERROR);
	}

	// Get wallet
	if (!$wallet = DB::queryFirstRow("SELECT * FROM coin_wallets WHERE id = %d", $addr_row['wallet_id'])) {
		trigger_error("Wallet does not exist, ID# $addr_row[wallet_id]", E_USER_ERROR);
	}

	// Multisig
	if ($wallet['address_type'] == 'multisig') { 

		// Go through addresses
		$public_keys = array();
		$rows = DB::query("SELECT * FROM coin_addresses_multisig WHERE address = %s ORDER BY id", $address);
		foreach ($rows as $row) { 
			$keyindex = $addr_row['is_change_address'] . '/' . $row['address_num'];
			$ext_pubkey = trim($enc->decrypt(DB::queryFirstField("SELECT public_key FROM coin_wallets_keys WHERE id = %d", $row['key_id'])));
			$child_pubkey = $this->build_key($ext_pubkey, $keyindex)[0];
			$public_keys[] = $this->import($child_pubkey)['key'];
		}

		// Create redeem script
		$scriptsig = $this->create_redeem_script($wallet['sigs_required'], $public_keys);

	// Standard
	} else { 
		$decode_address = $this->base58_decode($address);
		$scriptsig = '76a914' . substr($decode_address, 2, 40) . '88ac';
	}

	// Return
	return $scriptsig;

}

////////////////////////////////////////////////////////////////////////////
// Validate private key
////////////////////////////////////////////////////////////////////////////

public function validate_extended_private_key($ext_key, $is_public = false) { 

	// Check prefix
	if ($is_public === true) { 
		$prefix = TESTNET == 1 ? 'xpub' : 'xpub';
	} else { 
		$prefix = TESTNET == 1 ? 'tprv' : 'xprv';
	}
	if (!preg_match("/^$prefix/", $ext_key)) { return false; }
	if (strlen($ext_key) < 100 || strlen($ext_key) > 130) { return false; }

	// Decode key
	$hex = $this->base58_decode($ext_key);
	
	// Import key
	$import = $this->import($ext_key);

	// Initialize
	$g = SECcurve::generator_secp256k1();
	$n = $g->getOrder();
		
	// initialize the key as a base 16 number.
	$g_l = gmp_init($import['key'], 16);
	$_equal_zero = gmp_cmp($g_l, gmp_init(0,10));
	$_GE_n = gmp_cmp($g_l, $n);
		
	if ($_equal_zero == 0 || $_GE_n == 1 || $_GE_n == 0) { 	
		return false;
	} else { 
		return true;
	}
	
}

////////////////////////////////////////////////////////////////////////////
// Validate address
////////////////////////////////////////////////////////////////////////////

public function validate_address($address) { 

	// Decode
	$decode = $this->base58_decode($address); 
	if (strlen($decode) != 50) { return false; }

	// Compare address versionok\n
	$version = substr($decode, 0, 2);
	$p2sh_byte = TESTNET == 1 ? 'c4' : '05';
	if (hexdec($version) > hexdec($this->public_prefix) && $version != $p2sh_byte) { return false; }

	// Compare checksum
	$hash = hash('sha256', hash('sha256', pack("H*", substr($decode, 0, 42)), true));
	return substr($decode, -8) == substr($hash, 0, 8);
		
}

////////////////////////////////////////////////////////////////////////////
// Import
////////////////////////////////////////////////////////////////////////////

public function import($ext_key) { 

	// Decode key
	$hex = $this->base58_decode($ext_key);
	
	// Get magic byte info
	$key = array();
	$key['magic_bytes'] = substr($hex, 0, 8);
	
	// Set key variables
	$key['type'] = ($key['magic_bytes'] == '0488ade4' || $key['magic_bytes'] == '04358394') ? 'private' : 'public';
	$key['testnet'] = TESTNET;
	$key['network'] = 'bitcoin';
	$key['version'] = $this->public_prefix;
	$key['depth'] = gmp_strval(gmp_init(substr($hex, 8, 2), 16), 10);
	$key['fingerprint'] = substr($hex, 10, 8);
	$key['i'] = substr($hex, 18, 8);
	$key['address_number'] = $this->get_address_number($key['i']);
	$key['chain_code'] = substr($hex, 26, 64);

	// Get start position & offset
	if($key['type'] == 'public') {
		$key_start_position = 90;
		$offset = 66;
	} else {
		$key_start_position = 92;
		$offset = 64;
	}
	$key['key'] = substr($hex, $key_start_position, $offset);

	// Return
	return $key;

}

////////////////////////////////////////////////////////////////////
// Decompress pulic key
////////////////////////////////////////////////////////////////////

public function decompress_public_key($key) { 

	// Initialize
	$y_byte = substr($key, 0, 2);
	$x_coordinate = substr($key, 2);
		
	// Set variables
	$x = gmp_strval(gmp_init($x_coordinate, 16), 10);
	$curve = SECcurve::curve_secp256k1();
	$generator = SECcurve::generator_secp256k1();

	// Decode
	try {
		$x3 = NumberTheory::modular_exp( $x, 3, $curve->getPrime() );	
		$y2 = gmp_add($x3, $curve->getB());
		$y0 = NumberTheory::square_root_mod_prime(gmp_strval($y2, 10), $curve->getPrime());
		if ($y0 === false) { return false; }

		$y1 = gmp_strval(gmp_sub($curve->getPrime(), $y0), 10);
		$y_coordinate = ($y_byte == '02') ? ((gmp_Utils::gmp_mod2(gmp_init($y0, 10), 2) == '0') ? $y0 : $y1) : ((gmp_Utils::gmp_mod2(gmp_init($y0, 10), 2) !== '0') ? $y0 : $y1);
		$y_coordinate = str_pad(gmp_strval($y_coordinate, 16), 64, '0', STR_PAD_LEFT);
		$point = new Point($curve, gmp_strval(gmp_init($x_coordinate, 16), 10), gmp_strval(gmp_init($y_coordinate, 16), 10), $generator->getOrder());
	
	} catch (Exception $e) { return false; }
		
	// Return
	return array(
		'x' => $x_coordinate, 
		'y' => $y_coordinate,
		'point' => $point,
		'public_key' => '04' . $x_coordinate . $y_coordinate
	);
	
}

////////////////////////////////////////////////////////////////////
// Get Address Number
////////////////////////////////////////////////////////////////////

public function get_address_number($hex, $is_prime = 0) { 

	// Decode, if prime
	if ($is_prime == 1) { 
		$hex = str_pad(gmp_strval(gmp_sub(gmp_init($hex, 16), gmp_init('80000000', 16)), 16), 8, '0', STR_PAD_LEFT);
	}
	
	// Get number
	$dec = gmp_strval(gmp_init($hex, 16), 10);
	$n = $dec & 0x7fffffff;

	// Return
	return $n;
}

////////////////////////////////////////////////////////////////////////////
// Get address definition
////////////////////////////////////////////////////////////////////////////

public function get_definition_tuple($parent, $string_def) { 

	// Extract child numbers
	$address_definition = explode("/", $string_def);
		
	// Load the depth of the parent key.
	$import = $this->import($parent);
	$depth = $import['depth']; 
		
	// Start building the address bytes tuple
	foreach ($address_definition as &$def) {

		// Check if we want the prime derivation
		$want_prime = 0;
		if(strpos($def, "'") !== false) {
			// Remove ' from the number, and set $want_prime
			str_replace("'", '', $def);
			$want_prime = 1;
		}

		// Calculate address byres
		$and_result = ($want_prime == 1) ? $def | 0x80000000 : $def;
		$hex = unpack("H*", pack("N", $and_result)); 
		$def = $hex[1];
		$depth++;
	}
	
	// Reverse the array (to allow array_pop to work) and return.
	return array_reverse($address_definition);

}

////////////////////////////////////////////////////////////////////////////
// Encode
////////////////////////////////////////////////////////////////////////////

public function encode($data) {

	// Get magic bytes
	if ($data['testnet'] == 1) { 
		$magic_byte = $data['type'] == 'private' ? '04358394' : '043587cf';
	} else { 
		$magic_byte = $data['type'] == 'private' ? '0488ade4' : '0488b21e';
	}

	// Set variables
	$depth = str_pad(gmp_strval(gmp_init($data['depth'], 10), 16), 2, '0', STR_PAD_LEFT);
	$key_data = $data['type'] == 'public' ? $data['key'] : '00' . $data['key'];

	// Encode string	
	$string = $magic_byte . $depth . $data['fingerprint'] . $data['i'] . $data['chain_code'] . $key_data;
	return $this->base58_encode_checksum($string);

}

////////////////////////////////////////////////////////////////////////////
// BASE58 Encode Checksum
////////////////////////////////////////////////////////////////////////////

public function base58_encode_checksum($hex) { 

	// SHA256 Hash
	$checksum = hash('sha256', hash('sha256', pack('H*', $hex), true));
	$hash = $hex . substr($checksum, 0, 8);

	// Return
	return $this->base58_encode($hash);

}

////////////////////////////////////////////////////////////////////////////
// BASE58 Encode
////////////////////////////////////////////////////////////////////////////

public function base58_encode($hex) { 

	// Encode
	$num = gmp_strval(gmp_init($hex, 16), 58);
	$num = strtr($num, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv', '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz');

	// Pad leading 0s
	$pad = ''; $n = 0;
	while ($hex[$n] == '0' && $hex[$n+1] == '0') {
		$pad .= '1'; $n += 2;
	}
	
	// Return
	return $pad . $num;

}

////////////////////////////////////////////////////////////////////////////
// BASE58 Decode
////////////////////////////////////////////////////////////////////////////

public function base58_decode($base58) { 

	// Initialize
	$origbase58 = $base58;
	$return = "0";

	// Go through string
	for($i = 0; $i < strlen($base58); $i++) {
		$return = gmp_add(gmp_mul($return, 58), strpos('123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz', $base58[$i]));
	}
	$return = gmp_strval($return, 16);
		
	for($i = 0; $i < strlen($origbase58) && $origbase58[$i] == "1"; $i++) {
		$return = "00" . $return;
	}
	if(strlen($return) %2 != 0) { $return = "0" . $return; }
	
	// Return
	return $return;

}

////////////////////////////////////////////////////////////////////
// BIP32 - HEX Encode / Decode
////////////////////////////////////////////////////////////////////

public function hex_encode($number) { 
	$hex = gmp_strval(gmp_init($number, 10), 16);
	return (strlen($hex)%2 != 0) ? '0' . $hex : $hex;
}
	
public function hex_decode($hex) { 
	return gmp_strval(gmp_init($hex, 16), 10);
}


}

?>