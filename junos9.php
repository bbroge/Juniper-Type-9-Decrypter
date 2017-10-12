<!DOCTYPE html>
<html>
<body>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
Name: <input type="text" name="Password">
<input type="submit" name="juniper_decrypt" value="Decrypt!">
</form>

<?php
//Below functions ported to PHP from Python by Brent Broge
//Original Python script: https://github.com/mhite/junosdecode/blob/master/junosdecode.py
function _nibble($cref, $length) {
        $nib = substr($cref, 0, $length);
        $rest = substr($cref, $length);
        if (strlen($nib) != $length) {
                echo "Ran out of characters";
                exit(1);}
        return array($nib, $rest);
}

function _gap($c1, $c2) {
    $ALPHA_NUM = array("-"=>"37","/"=>"6","."=>"59","1"=>"16","0"=>"13","3"=>"3","2"=>"44","5"=>"63","4"=>"46","7"=>"35","6"=>"5","9"=>"7","8"=>"31","A"=>"9","C"=>"8","B"=>"15","E"=>"19","D"=>"53","G"=>"51","F"=>"2","I"=>"17","H"=>"56","K"=>"27","J"=>"49","M"=>"29","L"=>"32","O"=>"14","N"=>"36","Q"=>"0","P"=>"61","S"=>"22","R"=>"18","U"=>"52","T"=>"64","W"=>"30","V"=>"39","Y"=>"43","X"=>"33","Z"=>"50","a"=>"48","c"=>"21","b"=>"40","e"=>"26","d"=>"38","g"=>"45","f"=>"62","i"=>"55","h"=>"20","k"=>"57","j"=>"54","m"=>"60","l"=>"25","o"=>"47","n"=>"4","q"=>"58","p"=>"11","s"=>"42","r"=>"24","u"=>"12","t"=>"10","w"=>"41","v"=>"28","y"=>"23","x"=>"34","z"=>"1");
    $NUM_ALPHA = array('Q', 'z', 'F', '3', 'n', '6', '/', '9', 'C', 'A', 't', 'p', 'u', '0', 'O', 'B', '1', 'I', 'R', 'E', 'h', 'c', 'S', 'y', 'r', 'l', 'e', 'K', 'v', 'M', 'W', '8', 'L', 'X', 'x', '7', 'N', '-', 'd', 'V', 'b', 'w', 's', 'Y', '2', 'g', '4', 'o', 'a', 'J', 'Z', 'G', 'U', 'D', 'j', 'i', 'H', 'k', 'q', '.', 'm', 'P', 'f', '5', 'T');
    //return ($ALPHA_NUM[$c2] - $ALPHA_NUM[$c1]) % count($NUM_ALPHA) - 1;
    //implement fix for php modulus returning negative numbers
    return (count($NUM_ALPHA) + (($ALPHA_NUM[$c2] - $ALPHA_NUM[$c1]) % count($NUM_ALPHA))) % count($NUM_ALPHA) - 1;
}

function _gap_decode($gaps, $dec) {
    $num = 0;
    if (count($gaps) != count($dec)) {
        echo "Nibble and decode size not the same!";
        exit(1);}
    $count=count($gaps);
    for($i=0; $i<$count; ++$i) {
        $num += $gaps[$i] * $dec[$i];
		}
    return chr($num % 256);
}


function juniper_decrypt($crypt) {
echo "Encrypted Password: ".$crypt."<br />";
$encoding = array([1, 4, 32], [1, 16, 32], [1, 8, 32], [1, 64], [1, 32], [1, 4, 16, 128], [1, 32, 64]);
$chars = explode('$9$',$crypt,2);
$chars = _nibble($chars[1], 1);
$prev = $chars[0];
$extra = array('-'=> 1, '/'=> 3, '.'=> 0, '1'=> 2, '0'=> 3, '3'=> 3, '2'=> 1, '5'=> 0, '4'=> 1, '7'=> 1, '6'=> 3, '9'=> 3, '8'=> 2, 'A'=> 3, 'C'=> 3, 'B'=> 2, 'E'=> 2, 'D'=> 1, 'G'=> 1, 'F'=> 3, 'I'=> 2, 'H'=> 0, 'K'=> 2, 'J'=> 1, 'M'=> 2, 'L'=> 2, 'O'=> 3, 'N'=> 1, 'Q'=> 3, 'P'=> 0, 'S'=> 2, 'R'=> 2, 'U'=> 1, 'T'=> 0, 'W'=> 2, 'V'=> 1, 'Y'=> 1, 'X'=> 2, 'Z'=> 1, 'a'=> 1, 'c'=> 2, 'b'=> 1, 'e'=> 2, 'd'=> 1, 'g'=> 1, 'f'=> 0, 'i'=> 0, 'h'=> 2, 'k'=> 0, 'j'=> 1, 'm'=> 0, 'l'=> 2, 'o'=> 1, 'n'=> 3, 'q'=> 0, 'p'=> 3, 's'=> 1, 'r'=> 2, 'u'=> 3, 't'=> 3, 'w'=> 1, 'v'=> 2, 'y'=> 2, 'x'=> 2, 'z'=> 3);
$chars = _nibble($chars[1], $extra[$chars[0]]);

$decrypt = '';

while (strlen($chars[1])>0) {
$decode = $encoding[strlen($decrypt) % count($encoding)];
$chars = _nibble($chars[1], count($decode));
$gaps = [];
$strlen = strlen($chars[0]);
	for( $x = 0; $x<$strlen; $x++ ) {
    	$one = substr($chars[0], $x, 1 );
        $g = _gap($prev, $one);
		$prev = $one;
		$gaps[] = $g;
		}
	$decrypt .= _gap_decode($gaps, $decode);  
	}
echo "Decrypted Password: ".$decrypt."<br />";
return(1);
}

if ((isset($_POST['juniper_decrypt'])) and (strlen($_POST['Password'])>0))
  juniper_decrypt($_POST['Password']);
 else
  echo "Please type your Crypted (Junos $9$) password!";
?>

</body>
</html>
