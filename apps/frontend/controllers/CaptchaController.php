<?php
class CaptchaController extends ControllerBase
{
	private $width = 95;
	private $height = 38;
	private $counts = 4;     //验证码字符数
	private $distrubcode="1235467890qwertyuipkjhgfdaszxcvbnm"; //随机因子
	private $fonturl="./static/fonts/TektonPro-BoldCond.otf"; 

	//  第一种验证码
	public function aaAction(){
		$this->view->disable();
		Header("Content-type: image/GIF");
		$code = $this->getCode();
		$this->session->set('code',$code);
		$im=imagecreate($this->width,$this->height);
		$bgcolor = ImageColorAllocate($im, rand(200,255),rand(200,255),rand(200,255));
		imagefill($im,0,0,$bgcolor);
		$width=$this->width;
			$counts=$this->counts;
			$height=$this->height;
			$scode=$code;
			$y=floor($height/2)+floor($height/4);
			$fontsize=20;
			$fonturl="./static/fonts/AdobeGothicStd-Bold.otf";
			$counts=$this->counts;
			for($i=0;$i<$counts;$i++){
				$char=$scode[$i];
				$x=floor($width/$counts)*$i+8;
				$jiaodu=0;
				$color = ImageColorAllocate($im,rand(0,50),rand(50,100),rand(100,140));
				imagettftext($im,$fontsize,$jiaodu,$x,$y,$color,$fonturl,$char);
			}
		/*$count_h=$this->height;
		$cou=floor($count_h*2);
		for($i=0;$i<$cou;$i++){
			$x=rand(0,$this->width);
			$y=rand(0,$this->height);
			$jiaodu=rand(0,360);
			$fontsize=rand(8,15);
			$fonturl=$this->fonturl;
			$originalcode = $this->distrubcode;
			$countdistrub = strlen($originalcode);
			$dscode = $originalcode[rand(0,$countdistrub-1)];
			$color = ImageColorAllocate($im, rand(40,140),rand(40,140),rand(40,140));
			imagettftext($im,$fontsize,$jiaodu,$x,$y,$color,$fonturl,$dscode);
		}*/
		ob_clean();
		ImageGIF($im);
		ImageDestroy($im); 
	}
	private function getCode(){
			$originalcode = $this->distrubcode;
			$countdistrub = strlen($originalcode);
			$_dscode = "";
			$counts=$this->counts;
			for($j=0;$j<$counts;$j++){
				$dscode = $originalcode[rand(0,$countdistrub-1)];
				$_dscode.=$dscode;
			}
			return $_dscode;
			
	}

	// 第二种验证码
	public function ggAction()
	{
		$this->view->disable();
		$code = $this->getCode();
		$type = $_GET['type'];
		$this->session->set($type.'_code',$code);
		$im_x = 160;
		$im_y = 40;
		$im = imagecreatetruecolor($im_x,$im_y);
		$text_c = ImageColorAllocate($im, mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
		$tmpC0=mt_rand(100,255);
		$tmpC1=mt_rand(100,255);
		$tmpC2=mt_rand(100,255);
		$buttum_c = ImageColorAllocate($im,$tmpC0,$tmpC1,$tmpC2);
		imagefill($im, 16, 13, $buttum_c);
		$font = '/static/fonts/TektonPro-BoldCond.otf';
		for ($i=0;$i<strlen($code);$i++)
		{
			$tmp =substr($code,$i,1);
			$array = array(-1,1);
			$p = array_rand($array);
			$an = $array[$p]*mt_rand(1,10);//角度
			$size = 28;
			imagettftext($im, $size, $an, 15+$i*$size, 35, $text_c, $font, $tmp);
		}
		$distortion_im = imagecreatetruecolor ($im_x, $im_y);
		imagefill($distortion_im, 16, 13, $buttum_c);
		for ( $i=0; $i<$im_x; $i++) {
			for ( $j=0; $j<$im_y; $j++) {
				$rgb = imagecolorat($im, $i , $j);
				if( (int)($i+20+sin($j/$im_y*2*M_PI)*10) <= imagesx($distortion_im)&& (int)($i+20+sin($j/$im_y*2*M_PI)*10) >=0 ) {
					imagesetpixel ($distortion_im, (int)($i+10+sin($j/$im_y*2*M_PI-M_PI*0.1)*4) , $j , $rgb);
				}
			}
		}
		//加入干扰象素;
		$count = 160;//干扰像素的数量
		for($i=0; $i<$count; $i++){
			$randcolor = ImageColorallocate($distortion_im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($distortion_im, mt_rand()%$im_x , mt_rand()%$im_y , $randcolor);
		}
		$rand = mt_rand(5,30);
		$rand1 = mt_rand(15,25);
		$rand2 = mt_rand(5,10);
		for ($yy=$rand; $yy<=+$rand+2; $yy++){
			for ($px=-80;$px<=80;$px=$px+0.1)
			{
				$x=$px/$rand1;
				if ($x!=0)
				{
					$y=sin($x);
				}
				$py=$y*$rand2;

				imagesetpixel($distortion_im, $px+80, $py+$yy, $text_c);
			}
		}
		//设置文件头;
		Header("Content-type: image/png");
		//以PNG格式将图像输出到浏览器或文件;
		ob_clean();
		ImagePNG($distortion_im);
		//销毁一图像,释放与image关联的内存;
		ImageDestroy($distortion_im);
		ImageDestroy($im);
	}
}

