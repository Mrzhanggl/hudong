<?php


$myurlentre = "http://www.falv58.com/falv_exam_general/oauth2.php";//设置微信分享后点击进入的URL
$iipp = $_SERVER["REMOTE_ADDR"]; //获取用户IP

if(isset($_GET["code"]))
{
	$code = $_GET["code"];
	
	$useropenid = getUserInfoOauth2($code);
}
else
{
	$useropenid = "test"; //本地调试，获取不到的话用test表示openid
}

//echo $useropenid;
$companyamount = getamount();

function getUserInfoOauth2($code)
{
	$appid = "wxfa6962ec80df50f2";
	$appsecret = "dca0b90acfe3adaa79efe57f391c875f";

    //oauth2的方式获得openid
	$access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
	$access_token_json = https_request($access_token_url);
	//echo "tokenjson".$access_token_json."|";
	$access_token_array = json_decode($access_token_json, true);
	$openid = $access_token_array['openid'];
	return $openid;//静默获取openid的话到这步就结束了
	
	
	//未关注用户获取openid就到此为止
	$access_token = $access_token_array['access_token'];
	//echo "openid=".$openid;
	
	//oauth2方式获取用户信息
	$access_token = $access_token_array['access_token'];
	$userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
	$userinfo_json = https_request($userinfo_url);
	//echo "userinfo=".$userinfo_json."|";
	$userinfo_array = json_decode($userinfo_json, true);
	//return $userinfo_array; 要获取用户头像等资料则要执行到这一步
	
}

function https_request($url)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	if (curl_errno($curl)) {return 'ERROR '.curl_error($curl);}
	curl_close($curl);
	return $data;
}

function getamount()
{
	$config = array(
	'DB_HOST'	=>	'139.196.232.33',
	'DB_USER'	=>	'root',
	'DB_PWD'	=>	'Huihan2014',
	'DB_NAME'	=>	'falv_exam',
	'DB_PORT'	=>	3306
	);
	

	//连接数据库
	$con = new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PWD'],$config['DB_NAME']) or die('Could not connect: ' . mysqli_error());
	//选择并打开数据库
	//mysqli_select_db($con,$config['DB_NAME']);
	//sql语句
	$sql = "select sum(addamount) FROM ((select sum(a.`add`) as 'addamount' from add_amount a) union (select count(*) as 'addamount' from exam)) b";
	$con ->query("set names utf8mb4");
	$rs = $con->query($sql);
	//$rightnum = $rs->num_rows;
	$rowresult = mysqli_fetch_row($rs); 
	mysqli_free_result($rs);
	mysqli_close($con);
	return $rowresult[0];
}


require_once "jssdk.php";
$jssdk = new JSSDK("wxfa6962ec80df50f2", "dca0b90acfe3adaa79efe57f391c875f");//法驴服务号的ID，不要外泄，也不用改
$signPackage = $jssdk->GetSignPackage();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$myurl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

?>

<!DOCTYPE html>
<html> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	
	<title>企业专业法律体检 - 法驴内部版</title>
	
	<script type="text/javascript" src="./js/jquery-1.8.2.min.js"></script>
    <!--script type="text/javascript" src="js/ajax.js?ver=123123123"></script-->
	<script type="text/javascript" src="js/numrun.js?ver=123123123"></script>
	<script type="text/javascript" src="js/md5.js"></script>
	<script type="text/javascript">
	
	var openid = "<?php echo $useropenid?>";//全局变量，openid
	var wronganswersArray = new Array(); //全局变量，错题答案集
	//var wronganswersexpArray = new Array(); //全局变量，错题解析集
	//var wronganswersactArray = new Array(); //全局变量，错题措施集
	
	var orginalamount = <?php echo $companyamount?>; //全局变量，初始参与企业数增量
	var addamount = 0; //全局变量，参与企业数增量，在进入做题时上传服务器
	var iipp = '<?php echo $iipp?>';

	$(document).ready(function () {
　　$('body').height($('body')[0].clientHeight);
});

	</script>
</head>

<body style = 'margin:0px'>
	
	<div class="numberRun"></div><!--滚动数字-->
	<div>
		<img class = "companyintro" src = "images/page1/page1_12.png"><!--答题页头部文字-->
	</div>
	<div>
		<img class = "bottomblock" src = "images/page1/page1_2.png"><!--封面底部蒙版-->
	</div>
	<div>
		<img class = "logo" src = "images/page1/page1_14.png"><!--logo-->
	</div>

	<div class = "page_role">
		<!--img class = "imhr" src = "images/hr.png">
		<img class = "imboss" src = "images/boss.png"--><!--logo-->
		<div class = "role_photo">
			<img class = "role_boss" src = "images/role_page/role_boss.png">
			<img class = "role_hr" src = "images/role_page/role_hr.png">
			<img class = "role_sales" src = "images/role_page/role_sales.png">
		</div>
		<!--img class = "role_banner" src = "images/role_page/rolebanner.png"-->
		<div class = "role_head">
			<img class = "head_boss" src = "images/role_page/head_boss_c.png">
			<img class = "head_sales" src = "images/role_page/head_sales.png">
			<img class = "head_hr" src = "images/role_page/head_hr.png">

		</div>
		<img class = "role_button" src = "images/role_page/rolebutton.png">
		<div class = "role_blank">
		</div>
	</div>
	<div class="wrap">


		<div class="enter_wrap">
			<div class="wrap_center">
				<div class="enter_btn">
					<img class = "enter_btn" src = "images/page1/page1_1.png">
				</div>
			</div>
			<div class="wrap_bottom">

			</div>
		</div>

		<div class="topic_wrap">
			<div class="fade_wrap">
				<div class="wrap_top">
					<div class="topic_no">
						<span class="curr_no" id="curr_no">1</span>/<span class="count">20</span>
					</div>
				</div>

				<div class="wrap_top1">
					<div class="topic_title" id="topic_title"></div>
				</div>

				<div class="wrap_center" id="div1">

					<table class="topic_option" id="topic_option">

					</table>

				</div>
				<script>
					var oDiv = document.getElementById('div1');

					oDiv.onmousedown = function(){
					oDiv.style.color = '#E73146';
					}

				</script>

			</div>

			<div class="wrap_bottom">

			</div>
		</div>



		<div class="gameover">
			<div class="gameover_content">
				<img class = "companyintro2" src = "images/page2/page2_2.png">
				<div class = "input_div1">
					<img class = "input_frame1" src = "images/page2/page2_3.png">
					<input class="input_company" placeholder="公司名" type="text" id="input_company"/>
				</div>
				<div class = "input_div2">
					<img class = "input_frame2" src = "images/page2/page2_3.png">
					<input class="input_name" placeholder="联系人姓名" type="text" id="input_name"/>
				</div>
				<div class = "input_div3">
					<img class = "input_frame3" src = "images/page2/page2_3.png">
					<input class="input_contact" placeholder="联系方式" type="text" id="input_contact"/>
				</div>
				<div class = "input_div4">
					<img class = "input_frame4" src = "images/page2/page2_3.png">
				<input class="input_verifiedcode" placeholder="验证码" type="text" id="input_verifiedcode"/>
					<div class = "send_message" id = "send_message">
						<span class = "message_check" >发送</span>
					</div>
				</div>
				<!--div id = "fix-height" style = "position:relative;;height:300px; width: 100%; display: none">
				</div-->
				
		
				<img type = "button" class = "mysubmit" src = "images/page2/page2_1.png"><!--提交按钮-->
			</div>
		</div>
		<script type="text/javascript">
		</script>


		<div class="share_wrap">

			<div class="share_text">

				<img class="share_img" src="images/share.png">

			</div>

		</div>

		<div id="allDiv"  style="z-index:100;position:fixed; top:0px; left:0px; width:100%; height:100%; background:transparent;background-color:rgba(0,0,0,0.8); display:none;">
				<div>
					<a href="#"><img class="img_7" src="images/cover-trans.png" onclick="hidediv()" style="width: 100%;"></a>
				</div>
		</div>	
	</div>
	
	
	<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
	<script>
	
	
	wx.config({
			debug: false,
			appId: '<?php echo $signPackage["appId"];?>',
			timestamp: <?php echo $signPackage["timestamp"];?>,
			nonceStr: '<?php echo $signPackage["nonceStr"];?>',
			signature: '<?php echo $signPackage["signature"];?>',
			jsApiList: [
			// 所有要调用的 API 都要加到这个列表中
			'onMenuShareTimeline',
			'onMenuShareAppMessage',
			'onMenuShareQQ',
			'onMenuShareWeibo'
			]
		});
		//自定义内容

		var dataForWeixin = {
			title: '企业法律风险体检v2.0',
			desc: '法驴 - 您的企业贴身法律管家',
			link: '<?php echo $myurlentre; ?>',
			imgUrl:'http://www.falv58.com/falv_exam_general/images/logo200x200.png'//分享后的缩略图
		};
		wx.ready(function () {
			wx.onMenuShareTimeline({
				title: dataForWeixin.title, // 分享标题
				link: dataForWeixin.link, // 分享链接
				imgUrl: dataForWeixin.imgUrl,
				success: function () 
				{ 			
				// 用户确认分享后执行的回调函数
				},
				cancel: function () 
				{ 
				// 用户取消分享后执行的回调函数
				}
			});
			wx.onMenuShareAppMessage({
				title: dataForWeixin.title,
				desc: dataForWeixin.desc,
				link: dataForWeixin.link,
				imgUrl: dataForWeixin.imgUrl,
				success: function () 
				{ 
				// 用户确认分享后执行的回调函数
				},
				cancel: function () 
				{ 
				// 用户取消分享后执行的回调函数
				}
			});
			wx.onMenuShareQQ({
				title: dataForWeixin.title,
				desc: dataForWeixin.desc,
				link: dataForWeixin.link,
				imgUrl: dataForWeixin.imgUrl,
				success: function () 
				{ 
				// 用户确认分享后执行的回调函数
				},
				cancel: function () 
				{ 
				// 用户取消分享后执行的回调函数
				}
			});
			wx.onMenuShareWeibo({
				title: dataForWeixin.title,
				desc: dataForWeixin.desc,
				link: dataForWeixin.link,
				imgUrl: dataForWeixin.imgUrl,
				success: function () 
				{ 
				// 用户确认分享后执行的回调函数
				},
				cancel: function () 
				{ 
				// 用户取消分享后执行的回调函数
				}
			});
		});
	</script>
</body>
<script type="text/javascript" src="./js/js.js"></script>
</html> 






