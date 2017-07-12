<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 2017/7/11
 * Time: 下午4:49
 */
class kod_tool_imageVerifyCode{
    private function putSomeNothing(&$myImage,$width,$height,$times){
        ImageColorAllocate($myImage, 255, 255, 255);
        for($i=0;$i<$times;$i++){
            imagesetthickness($myImage,rand(1,5));
            imagearc($myImage,
                rand(0,$width),
                rand(0,$height),
                rand($height/2,$height),
                rand($height/2,$height),
                rand(0,180),
                rand(0,180),
                imagecolorallocate($myImage,rand(120,255),rand(120,255),rand(120,255))
            );
            imagesetthickness($myImage,rand(1,5));
            imageline($myImage,
                rand(0,$width),
                rand(0,$height),
                rand(0,$width),
                rand(0,$height),
                imagecolorallocate($myImage,rand(120,255),rand(120,255),rand(120,255))
            );
            $posX = rand(0,$width);
            $posY = rand(0,$height);
            for($j=0;$j<2;$j++){
                imagefilledrectangle($myImage,$posX,$posY,$posX+3,$posY+3,imagecolorallocate($myImage,rand(120,255),rand(120,255),rand(120,255)));
            }
        }
    }
    public function getImage($str,$width,$height,$paddingX=60){
        $strArr = array();
        for($i=0;$i<mb_strlen($str);$i++){
            $strArr[] = mb_substr($str,$i,1);
        }
        $myImage=ImageCreate($width,$height);
        $this->putSomeNothing($myImage,$width,$height,5);
        $allWordWidth = $width-$paddingX*2;
        foreach($strArr as $k=>$word){
            $worldColor = imagecolorallocate($myImage,rand(0,200),rand(0,200),rand(0,200));
            $fontSize = rand($height/2.5,$height/1.5);
            $xMove = min($allWordWidth/count($strArr),$fontSize)/5;
            $x = $allWordWidth/count($strArr)*($k+0.5) + rand(-$xMove,$xMove)+$paddingX;
            $y = ($height+$fontSize)/2+rand(-10,10);
            imagettftext($myImage, $fontSize, rand(-20,20), $x, $y, $worldColor, KOD_DIR_NAME.'/tool/imageVerifyCode.ttf',$word);
        }
        $this->putSomeNothing($myImage,$width,$height,3);
        header("Content-type:image/png");
        ImagePng($myImage);exit;
    }
}