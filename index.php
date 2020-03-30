<?php
//------------インクルード-----------------//
include("pre.php");
include("define.php");//サーバ定義
//配列をファイルから読み込み
$_set = unserialize(file_get_contents("_set.dat"));//設定データ
$_set2 = unserialize(file_get_contents("_set2.dat"));//設定データ
//配列をファイルから読み込み
$_entry = unserialize(file_get_contents("_entry.dat"));//入室データ
//print_r($_entry);

$NT=date("Y-m-d");
$NT2=date("H:i");
$TM=time();
$ac_f=urldecode($_GET[ac]);
$url = basename($_SERVER["REQUEST_URI"]);
$user=urldecode($_COOKIE["user"]);

//-----------ユーザー認証-------------------------//
if (isset($_COOKIE["mid"])){
	$mid = $_COOKIE["mid"];
	//$mid="2127";
	$html=new showHtml();
	$person=$html->showPh($mid);
	//print_r($person);
}else{
	$mid="0";
}	
//$mid="0";//debug

//exit;
/*
$url = basename($_SERVER["REQUEST_URI"]);
$logout = "login.php?url=".urlencode($url)."&logout=1";
//echo "XXX".$_COOKIE["user"]."XXX";
if (isset($_COOKIE["user"])){
	$user = urldecode($_COOKIE["user"]);
}else{
	//header( "Location: ./login.php?url={$_SERVER['PHP_SELF']}?logout=1" );
	//header( "Location: ../login.php?url=tmp001/check001?logout=1" );
	echo "<script>location.href = '{$logout}';</script>";	
	exit;
}	
*/

//------------POST処理-----------------//
/*
if($_POST[ac]<>""):
	list(,$_mid)=explode("\t",$_POST[ac]);
	$ac_en=urlencode($_POST[ac]);
	echo "<script>location.href = 'indexb2.php?mid={$_mid}&ac={$ac_en}';</script>"; 
endif;
*/
//$person=showPh($mid);

//============================================//
//---------------設定-------------------------//
//============================================//
if($_POST[action]<>""):
	//echo $_POST[action];exit;

  	//配列をファイルから読み込み
  	$_set = unserialize(file_get_contents("_set.dat"));

	//print_r($_POST[room]);
	foreach ($_POST[room] as $key => $value) {
		$_set[$key][room]=$_POST[room][$key];
		$_set[$key][capa]=$_POST[capa][$key];
	}
	$_set2[info]=$_POST[info];
	//print_r($_set);

	if($_POST[action]=="1"):
	  	//配列の中身をファイルに保存
	  	file_put_contents("_set.dat", serialize($_set));
	  	file_put_contents("_set2.dat", serialize($_set2));
	elseif($_POST[action]=="2")://リセット
		$_set="";//初期化
	  	file_put_contents("_set.dat", serialize($_set));
		$_set2="";//初期化
	  	file_put_contents("_set2.dat", serialize($_set2));
		$_entry="";//初期化
	  	file_put_contents("_entry.dat", serialize($_entry));
  	endif;

  	echo "<script>location.href = 'index';</script>"; 

endif;

//============================================//
//---------------一般-------------------------//
//============================================//
if($_POST[ac]<>""):
	//echo $_POST[ac];exit;
	$user_en=urlencode($_POST[ac]);
	echo $string2 = <<< EOM
	<!--クッキー-->
	<script type="text/javascript">
	<!--
	var name = "user";// クッキーの名前
	var value = "{$user_en}";// クッキーの値
	//var period = -10;	// 有効期限日数
	var period = 1;	// 有効期限日数
	// 有効期限の作成
	var nowtime = new Date().getTime();
	var clear_time = new Date(nowtime + (60 * 60 * 24 * 1000 * period));
	var expires = clear_time.toGMTString();

	// クッキーの発行（書き込み）
	document.cookie = name + "=" + escape(value) + "; expires=" + expires;
	// -->
	</script>
	<!--クッキー-->
EOM;
	echo "<script>location.href = '{$url}';</script>"; 
endif;

//============================================//
//---------------入室-------------------------//
//============================================//
if($_POST[entry]<>""):
	//echo $_POST[action];exit;

  	//配列をファイルから読み込み
  	$_entry = unserialize(file_get_contents("_entry.dat"));

	if($mid==0){
		$mid=urlencode($user);
		$person[0]=$user;
		$person[1]="man.png";				
	}

  	//入室記録を削除
	foreach ($_entry as $key => $value) {
		foreach ($value as $key2 => $value2) {
			//echo $key2;
			if($key2==$mid){
				unset($_entry[$key][$key2]);
			}
		}
	}

  	//入室記録
  	$_entry[$_POST[entry]][$mid]="{$person[0]}\t{$person[1]}\t{$TM}";

  	//配列の中身をファイルに保存
  	file_put_contents("_entry.dat", serialize($_entry));		
endif;

//============================================//
//---------------出力-------------------------//
//============================================//

//------------設定画面-----------------//
if($_GET[set]==1):
	$html=new showHtml();
	$html->html_h();
	$html->html_c2();
	//$html->html_f();
endif;

//------------部屋画面-----------------//
if($_GET[set]<>1):
	$html=new showHtml();
	$html->html_h();
	$html->html_c($mid);
	//$html->html_r();
	$html->html_f();
endif;

//============================================//
//---------------HTML-------------------------//
//============================================//

class showHtml{

	//-----------mimi写真取得-------------------------//

	public function showPh($mid){
		
		global $FILE_DOMAIN;

		//------------オートコンプリート-----------------//
		set_include_path(get_include_path() . PATH_SEPARATOR . PATH); //ライブラリパスを追加
		include_once 'Crypt/Blowfish.php';
		//include_once 'Crypt/BlowfishOld.php';

		//データベースに接続 //////////////////////////////////////
		$con = mysql_connect(DB_SERVER,DB_USER,DB_PW);
		///////////////////////////////////////////////////////////

		//UTF8の文字化け対策
		mysql_query('SET NAMES utf8', $con); // ←これ
			
		//データベースを選択////////////////////////////////////////
		mysql_select_db(DB_NAME, $con);
		////////////////////////////////////////////////////////////

		//SQL文をセット/////////////////////////////////////////////
		//$quryset = mysql_query("SELECT * FROM  `member` ORDER BY  `u_datetime` ASC LIMIT 0 , 300;");
		//$quryset = mysql_query("SELECT * FROM `c_member_secure` LIMIT 0, 500;");
		// FROM 表名1 INNER JOIN表名2 ON 表名1.フィールド名 = 表名2.フィールド名
		$quryset = mysql_query("
		SELECT  `c_member_id` ,  `nickname` ,  `image_filename` 
		FROM  `c_member` 
		WHERE  `c_member_id` =".$mid."
		LIMIT 0 , 1");

		////////////////////////////////////////////////////////////

		//１ループで１行データが取り出され、データが無くなるとループを抜
		while ($data = mysql_fetch_array($quryset)){

			/*
			// Blowfishデコード
			$blowfish = new Crypt_Blowfish(ENCRYPT_KEY);
			//$blowfish = new Crypt_BlowfishOld(ENCRYPT_KEY);
			$bindata = $data[4]; // DBからselectした結果など
			$decoded = base64_decode($bindata); // バイナリに戻す
			$decrypted = $blowfish->decrypt($decoded);
			*/
		        //列9を出力//////////////
		        $MIid = mb_convert_encoding($data[0], "utf-8", "auto");
		        //////////////////////////

		      //列9を出力//////////////
		        $MIname = mb_convert_encoding($data[1], "utf-8", "auto");
		        //////////////////////////

		      //列9を出力//////////////
		        $MIface = mb_convert_encoding($data[2], "utf-8", "auto");
		        // m_1_1466069920.jpg
		        //////////////////////////
		}

		$ext = substr($MIface, strrpos($MIface, '.') + 1);
		$ph="{$FILE_DOMAIN}/var/img_cache/{$ext}/w76_h76/img_cache_{$MIface}";
		$ph=str_replace(".{$ext}", "_{$ext}.{$ext}", $ph);
		return array($MIname,$ph);
	}

	//-----------htmlヘッダー-------------------------//

	public function html_h(){

		echo $html_h=<<<EOM
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>部屋管理くん</title>
		<!--jquery
		<script type="text/javascript" src="//code.jquery.com/jquery-2.2.4.min.js"></script>--->
		<!--materializecss-->
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
		<script src="//cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
		<!--fontawesome-->
		<link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome-animation/0.0.10/font-awesome-animation.css" type="text/css" media="all" />	
		<script type="text/javascript" src="/etc/lib/ten.js"></script>
		<!--animate.css-->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.0/animate.min.css">

		<!--materializecss-->
		<script>
		$(document).ready(function(){
			//ドロップダウンメニュー初期化
			//$(".dropdown-trigger").dropdown();	
			//ハンバーガーメニュー初期化
		  //$('.sidenav').sidenav();
			//セレクト初期化
		  //$('select').formSelect();
		  //テキストエリア初期化
			//$('input#input_text, textarea#textarea1').characterCounter();
			//$('input#input_text, textarea#textarea2').characterCounter();
		  M.AutoInit();//すべてのイニシャライズをまとめて行う!
		    //デートピッカー
		  //$('.datepicker').datepicker();
		  $('.datepicker').datepicker({
		    i18n:{
		        months:["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
		        monthsShort: ["1/", "2/", "3/", "4/", "5/", "6/", "7/", "8/", "9/", "10/", "11/", "12/"],
		        weekdaysFull: ["日曜日", "月曜日", "火曜日", "水曜日", "木曜日", "金曜日", "土曜日"],
		        weekdaysShort:  ["日", "月", "火", "水", "木", "金", "土"],
		        weekdaysAbbrev: ["日", "月", "火", "水", "木", "金", "土"],
		        cancel:"キャンセル",
		    },
		    format: "yyyy-mm-dd",
		  });
		  //タイムピッカー
		  $('.timepicker').timepicker();

		  //ツールチップ
    	　//$('.tooltipped').tooltip();

		  //オートコンプリート
		  $('input.autocomplete').autocomplete({
		  data: {
		    //"Apple": null,
		    //"Microsoft": null,
		    //"Google": 'https://placehold.it/250x250'
		    {$ac}
		  },
		　});

		});

		$(document).ready(function() {
			$('t1, textarea#t1').characterCounter();
			$('t2, textarea#t2').characterCounter();
			$('t3, textarea#t3').characterCounter();
			$('t4, textarea#t4').characterCounter();
		});

		</script>

		<!--スムーズスクロール-->
		<script>
		$(function(){
			$("a#arrow[href^='#']").click(function() {
				// #で始まるアンカーをクリックした場合に処理
				var speed = 500; // ミリ秒
				// スクロールスピード
				var href= $(this).attr("href");
				// アンカーの値を取得
				var target = $(href == "#" || href == "" ? 'html' : href);
				// 移動先取得
				var position = target.offset().top;
				// 移動先を数値で取得
				$('body,html').animate({scrollTop:position}, speed, 'swing');
				// スムーススクロール実行
				return false;
			});
		});
		</script>

		<!--POPUP-->
		<link rel="stylesheet" type="text/css" href="/lib/js/fb//jquery.fancybox-1.3.4.css" media="screen" />
		<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js?ver=1.6.3"></script>-->
		<script type="text/javascript" src="/lib/js/fb//jquery.gptop-1.0.js?ver=3.3"></script>
		<script type="text/javascript" src="/lib/js/fb//jquery.fancybox-1.3.4.pack.js?ver=3.3"></script>

		<script type="text/javascript">
			jQuery(function($) {
			$('#goto_top').gpTop();
			$('.iframe').fancybox({
			maxWidth	: 800,
			maxHeight	: 600,
			fitToView	: false,
			width		: '90%',
			height		: '90%',
			autoSize	: false,
			closeClick	: false,
			openEffect	: 'none',
			closeEffect	: 'none'
			});
			$('.over').fancybox({
			'titlePosition'  : 'over'
			});
			$('a[rel*=lightbox]').fancybox(); 
			});
		</script>
		<!--POPUP-->

		<!--POPUP-->
		<!--<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js'></script>-->
		<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/lity/1.6.6/lity.css' />  
		<script src='https://cdnjs.cloudflare.com/ajax/libs/lity/1.6.6/lity.js'></script>
		<style>
		.lity-iframe .lity-container {
		    max-width: 100% !important;
		}
		.lity-iframe-container {
		    _padding-top: 80% !important;
		    padding-top: 100vh !important;    
		}
		.lity-iframe-container iframe {
		    background: transparent !important;
		}
		.lity-close{
		    font-size:75px !important;
		    color:white !important;
		    margin:5px 25px 5px 5px !important;
		}   
		.lity-container {
		    z-index: 9992;
		    position: relative;
		    text-align: left;
		    vertical-align: top;
		    padding-top:10px;
		    padding-top:0px;
		} 
		 /*クローズボタンカスタマイズ*/
		  .lity-close{
		  font-size: 0px !important;
		    color: white !important;
		    margin: 36px 60px 5px 5px !important;
		  }
		  .lity-close::before {
		  content:url(close.png);
		  opacity:0.5;
		  }

		.lity-container {
		    z-index: 9992;
		    position: relative;
		    text-align: left;
		    vertical-align: top;
		    padding-top:10px;
		    padding-top:0px;
		} 
		</style>

		<!--サプミットボタン・エンター無効化-->
		<script>
		    $(function(){
		        $("input").on("keydown", function(e) {
		            if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
		                return false;
		            } else {
		                return true;
		            }
		        });
		    });
		</script>

		<script>
		// ページをreloadする方法
		function doReload() {		 
		    // reloadメソッドによりページをリロード
		    //window.location.reload();
		    //window.location = "{$url}";
		}		 
		window.addEventListener('load', function () {		 
		    // ページ表示完了した5秒後にリロード
		    //setTimeout(doReload, 5000);
		});
		</script>

		<script>
		// 部分リロード
		function reloadHoge() {
		  $.get(document.URL).done(function(data, textStatus, jqXHR) {
		    const doc = new DOMParser().parseFromString(data, 'text/html');
		    $('.hoge').html(doc.querySelector('.hoge').innerHTML);
		  });
		}
		setInterval(reloadHoge, 60000);
		</script>

		<style>
		body,td{
			font-size:13px;font-family:"游ゴシック", "Yu Gothic", YuGothic,meiryo;padding:5px;
			font-weight:500;
		}
		textarea{
			background-color:brown;
			background-color:#00838f;
			color:white;
			font-size:13px;
		}
		input{
			color:#880e4f
		}
		h1{
			font-size:17px
		}
		td{
			_width:250px
		}

		input0,textarea{
		    font-size:13px;
		    font-family: "游ゴシック", "Yu Gothic", YuGothic,'ヒラギノ角ゴ Pro W3', 'Hiragino Kaku Gothic Pro', 'Hiragino Kaku Gothic ProN', 'メイリオ', Meiryo;
		    border: 1px solid #B9C9CE;
		    border-radius:5px;
		    padding: 12px 0.8em;
		    box-shadow: inset 0 1px 2px rgba(0,0,0,0.2);
		}
		textarea:focus {
		  border-color:#83B6C2;
		    outline:none;
		    box-shadow:2px 2px 4px rgba(143,183,222,0.6),-2px -2px 4px rgba(143,183,222,0.6),inset 0 1px 2px rgba(0,0,0,0.2);
		}
			
		#a2{
			text-align:center;	
			text-decoration:none;	
			padding:2px 5px;
			border:1px solid white;
			background-color:green;
			color:white;
			filter:alpha(opacity=50);
			-moz-opacity: 0.5;
			opacity: 0.5;
			white-space: nowrap;  
			width:190px;
			_padding:30px;
			font-size:13px;
		}	
		#a3{
			font-size:13px;
			text-align:center;	
			text-decoration:none;	
			padding:2px 5px;
			border:1px solid white;
			background-color:red;
			color:white;
			filter:alpha(opacity=50);
			-moz-opacity: 0.5;
			opacity: 0.5;
			white-space: nowrap;  
			width:190px;
			_padding:30px;
		}
				
		#a3b{
			font-size:13px;
			text-align:center;	
			text-decoration:none;	
			padding:2px 5px;
			border:1px solid white;
			background-color:red;
			color:aqua;
			filter:alpha(opacity=50);
			-moz-opacity: 0.5;
			opacity: 0.5;
			white-space: nowrap;  
			width:190px;
			_padding:30px;
		}
		#contents{
			background:#b0c4de;
			margin:0px !important;
			padding:20px;
			background:rgba(255,255,255,1);
		}
		body{
			padding:0px !important;
			background:#b0c4de;
			margin:0px !important;
		}
		#header{
			background:steelblue;
			margin:0px !important;
			padding:3px 20px;
		}			
		#header h1{
			color:white;
			font-size:33px;
			font-family: "游明朝体", "Yu Mincho", YuMincho, "Hiragino Kaku Gothic ProN", "Hiragino Kaku Gothic Pro", "メイリオ", Meiryo, "ＭＳ ゴシック", sans-serif;
		}		
		#xx1:hover{
		 background-color:green !important;
		}		
		#xx2:hover{
		 background-color:orange !important;
		}		
		textarea{
			height:200px;
		}
		.row{
			margin:40px 0px;
		}

		.container {
		    margin: 0 auto;
		    max-width: 1280px;
		    width: 100%;
		}
		@media only screen and (min-width: 993px){
			.container {
			    width: 80%;
			}
			.rrr{
				text-align:right;
			}
		}
		label{
			color:#00838f;
			font-weight:700;
		}
		nav{
			text-align:center;
			_height:100px;
		}
		nav p{
			font-size:33px;
		}
		body {
		    padding: 0px !important;
		    background: silver;
		    margin: 0px !important;
		}
		.bb{
			font-size:33px;
			text-align:left;
		}
		.bb1{
			font-size:23px;
			float:left;
			text-align:right;	
		    font-family: FontAwesome,"Times New Roman","_ヒラギノ明朝 ProN W6", "_HiraMinProN-W6","_UD デジタル 教科書体 NK-R","游明朝体", "Yu Mincho", YuMincho,"游ゴシック体", YuGothic, "游ゴシック Medium", "YuGothic M","ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro", "メイリオ", Meiryo, sans-serif !important;
		}
		::placeholder {
		  color: aliceblue;
		  font-size: 1.2em;
		    font-family: FontAwesome,"Times New Roman","_ヒラギノ明朝 ProN W6", "_HiraMinProN-W6","_UD デジタル 教科書体 NK-R","游明朝体", "Yu Mincho", YuMincho,"游ゴシック体", YuGothic, "游ゴシック Medium", "YuGothic M","ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro", "メイリオ", Meiryo, sans-serif !important;

		}
		.f1 {
		    background: rgba(255,255,255, 0.1);
		    padding: 20px;
		    margin: -30px -7px -15px -7px;
		    color: silver !important;
		    border-radius: 10px;
			box-shadow: 2px 2px 4px inset;
		}
		.f2 {
		    background: rgba(255,255,255, 0.1);
		    padding: 20px;
		    margin: -30px -7px -15px -7px;
		    color: rgba(255,255,255,.7) !important;
		    border-radius: 10px;
			box-shadow: 2px 2px 4px inset;
		    border-radius: 20px;
	    	box-shadow: 0 10px 20px -10px #777777;
		}
		.min{
		    font-family: FontAwesome,"Times New Roman","ヒラギノ明朝 ProN W6", "HiraMinProN-W6","_UD デジタル 教科書体 NK-R","游明朝体", "Yu Mincho", YuMincho,"游ゴシック体", YuGothic, "游ゴシック Medium", "YuGothic M","ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro", "メイリオ", Meiryo, sans-serif !important;
		    font-weight: normal !important; 
		} 
		body{
			background: url(bg.jpg) transparent;
		}
		.ph1 {
		    border-radius: 30px;
	    	box-shadow: 0 10px 20px -10px #777777;
	    	margin:3px;
	    	width:30px;
	    	height:30px;
		}
		</style>	
		</head>
EOM;
	}

	//-----------htmlメインー-------------------------//

	public function html_c($mid){//メイン画面

		global $_set;
		global $_set2;
		global $user;
		global $NT2;

		$html=new showHtml();//mimiIDあり!
		$person=$html->showPh($mid);
		//print_r($person);

		if($mid==0)://mimiIDなし!
		if($user<>""){
			echo "<style>.kakutei{display:none;}</style>";
		}
		$name=<<<EOM
			<form action="{$url}" method="post" enctype="multipart/form-data" class="animated fadeIn">

			<div class="f1 row" style="margin:0px 0px;">
		    <div class="col s12">
		      <div class="row">
		        <div class="input-field col s7">
		          <i class="material-icons prefix">account_circle</i>
		          <input name=ac type="text" id="autocomplete-input" class="autocomplete" value="{$user}" autocomplete="off">
		          <label for="autocomplete-input">名前</label>
		        </div>
		        <div class="input-field col s3">
		        	<button class="btn waves-effect waves-light _btn-small cyan darken-3 kakutei" type="submit" name="action">確定
		    		<i class="material-icons right">send</i>
		  			</button>
				</div>        
		      </div>
		    </div>
		  	</div>

		  	</form>
EOM;
		else:
		
		$name=<<<EOM
			<form action="{$url}" method="post" enctype="multipart/form-data" class="animated fadeIn _rollIn">

			<div class="f1 row" style="margin:0px 0px;padding:10px;">
		    <div class="col s12">
		      <div class="row" style="margin:0px 0px;">
		        <div class="input-field col s12">
		        	<img src="{$person[1]}" style='width:56px;padding-right:10px;'><span class=_TEN style='font-size:27px;'>{$person[0]} {$mid}</span>
		        </div>
		      </div>
		    </div>
		  	</div>

		  	</form>
EOM;
		endif;

		//print_r($_set);	
		foreach ($_set as $key => $value) {
			if($value[room]==""){continue;}
			$btn.=<<<EOM
			<button onClick="sound()" class="btn waves-effect waves-light btn-large {$pulse}" type="submit" name="entry" value="{$value[room]}" style='margin:6px;'>入室{$value[room]}<i class="material-icons right">send</i></button>
EOM;
		}

		$html_r=$html->html_r();
		
		//メイン画面
		echo $html_c=<<<EOM
		<BODY><DIV class="container animated fadeIn">

			<nav class="cyan darken-2">
			<p style="font-weight:700;_text-shadow: #fff 0px 1px 2px, #000 0px -1px 1px;"><a class=min href={$url}>部屋定員管理くん <span class=TEN>{$_NT2}</span></a></p>
			</nav>

			<DIV id=contents>

			{$name}

			<form action="{$url}" method="post" enctype="multipart/form-data" class=hoge>

			<!-- 音声ファイルの読み込み -->
			<audio id="sound-file" preload="auto">
				<source src="1.mp3" type="audio/mp3">
				<!--<source src="1.wav" type="audio/wav">-->
			</audio>
			<script>
			function sound(){
			// [ID:sound-file]の音声ファイルを再生[play()]する
			document.getElementById( 'sound-file' ).play() ;			}
			</script>

		    <div class="row">
			    <div class="col s12 l12">
			    {$btn}
			    </div>    	
			</div>

			{$html_r}	

			</form>

			<div class="progress" style='margin-top:40px;'>
      			<div class="indeterminate"></div>
  			</div>
			<p class=min style="margin-top:20px;font-size:27px;color:#006064;font-weight:700"><i class="material-icons">info</i>{$_set2[info]}</p>

			</DIV>
EOM;
	}

	//-----------部屋状況画面-------------------------//

	public function html_r(){//部屋状況画面

		global $_set;
		//print_r($_set);
		global $_entry;
		//print_r($_entry);		

		foreach ($_set as $key => $value) {
			$person_s="";//初期化
			$cnt="";//初期化

			if($value[room]==""){continue;}
			foreach ($_entry[$value[room]] as $key2 => $value2) {
				list($name,$photo,$time)=explode("\t",$value2);
				//$time_df=time()-$time;
				$time_df=date("H:i",$time);
				$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-tada animated _tooltipped' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				$cnt++;
			}
			//print_r($person_s);

			//定員増減
			$def=$value[capa]-$cnt;
			$def2=$cnt/$value[capa];//充足率
			//$def2=0.1;
			if($def2>1){
				$def2_c="#d81b60";
				$def2_b="";				
			}elseif($def2>=0.8){
				$def2_c="#f50057";
				$def2_b="";
			}elseif($def2>=0.5){
				$def2_c="#e65100";
				$def2_b="";
			}elseif($def2>0){
				$def2_c="#00acc1";
				$def2_b="img/1.png";
			}else{
				$def2_c="#fff";
				$def2_b="img/1.png";				
			}
			$room[$value[room]]=<<<EOM
			<div class="f2 row animated _fadeIn flipInX" style="margin:10px 0px;background:{$def2_c} url({$def2_b}) no-repeat right top">
			    <div class="col s12">
			    <h3>{$value[room]} <span class="min TEN">残り {$_value[capa]} {$def}</span></h3>
			    {$person_s}
			  	</div>
		  	</div>
EOM;
		}
		$room_all=implode("",$room);
		return $room_all;
	}

	//-----------html設定画面-------------------------//

	public function html_c2(){//設定画面

		global $_set;
		global $_set2;

		for($i = 0; $i < 9; $i++){
			$i2=$i+1;
			$input_s.=<<<EOM
			    <div class="row">
				    <div class="col s2 l1">
				    	<i style='color:silver;' class="material-icons">filter_{$i2}</i>
				    </div>
				    <div class="col s4 l4">
						<input type="text" name="room[]" value="{$_set[$i][room]}" placeholder="名称{$i2}" style="padding-left:5px"/>
			 		</div>
				    <div class="col s4 l4"> 		
						<input type="number" name="capa[]" value="{$_set[$i][capa]}" placeholder="定員{$i2}" style="padding-left:5px"/>
					</div>
				</div>	
EOM;
		}

		echo $html_c2=<<<EOM
		<style>
		body{
			background: none rgba(0,0,0,1);
		}
		#contents{
			background: none rgba(0,0,0,1);
		}
		input {
		    color: darkkhaki;
		    font-size:22px !important;
		    text-align:center;
		}
		.lity-container {
		    z-index: 99999992;
		    position: relative;
		    text-align: left;
		    vertical-align: top;
		    padding-top:10px;
		    padding-top:0px;
		}
		.btn{
			margin:5px;
		} 
		</style>
		<BODY><DIV class="container">

			<DIV id=contents>

			<form action="{$url}" method="post" enctype="multipart/form-data" class="animated fadeIn">

			<div style="margin:50px 0px;text-align:center">
			
			{$input_s}
			
			<button style="margin-left:-20px;" class="btn waves-effect waves-light btn-large pulse" type="submit" name="action" value=1>確定<i class="material-icons right">send</i></button>
			<a href="javascript:history.back();"><button style="margin-right:10px;" class="btn waves-effect waves-light btn-large blue-grey darken-2" type="button" name="" value=>戻る<!--<i class="material-icons right">send</i>--></button></a>
			<button class="btn waves-effect waves-light btn-large pink darken-3" type="submit" name="action" value=2>リセット<i class="material-icons right">send</i></button>
			<p style='color:pink'>リセット押すと、データが初期化されます</p>
			</div>

			<div class="row">
			    <form class="col s12">
			      <div class="row">
			        <div class="input-field col s12">
			          <textarea name=info id="textarea1" class="materialize-textarea">{$_set2[info]}</textarea>
			          <label for="textarea1">お知らせ</label>
			        </div>
			      </div>
			    </form>
			 </div>

			<p style='height:30px;'></p>
			</form>
			</DIV>

		</DIV></BODY>
		</html>		
EOM;
	}

	//-----------htmlフッター-------------------------//

	public function html_f(){
		global $HP_DOMAIN;
		echo $html_f=<<<EOM
		    <footer class="page-footer">
		      <div class="container2" style='width:97%'>
		        <div class="row">
		          <div class="col l6 s12">
		            <p><i class="0large medium material-icons" style='float:left'>blur_linear</i>
		            <span class='min' style='font-size:19px;font-weight:_bold'>部屋定員管理くん</span>
		            <br><span class='min' style='font-size:19px;font-weight:_bold'>練習会オペ用</span>
		            <br/><a style="color:white;" href=https://github.com/lavierx/room>https://github.com/lavierx/room</a>
		            </p>
		            <br clear=all>     
		            <p class="grey-text text-lighten-4 addr">　<i class="material-icons">build</i><a style='color:white;font-size:19px;' href={$url}?set=1 _data-lity>部屋設定</a></p>
		          </div>
		          <div class="col l4 offset-l2 s12">
		            <p class="white-text"><i class="fas fa-info fa-fw"></i>お問い合わせ</p>
		            <ul>
		              <li><a class="grey-text text-lighten-3" href="mailto:info@{$HP_DOMAIN}"><i class="fa fa-envelope"></i> info@{$HP_DOMAIN}</a></li>
		            </ul>
		          </div>
		        </div>
		      </div>
		      <div class="footer-copyright">
		        <div class="container2" style="text-align:center">
		        <!--© 2018 Copyright Text-->
		        <span class="_min" id=cc>　ピアノサークル ビアノを弾きたい!</span>
		        <a class="grey-text text-lighten-4 right min" href="https://{$HP_DOMAIN}"><i class=min>official site</i></a>
		        </div>
		      </div>
		    </footer>

			<!--移動-->
			<div style="position:fixed; bottom:5px; right:10vw"><a id=arrow href=#><i class="fas fa-angle-up" style="opacity:0.3;font-size:80px !important;"></i></a> <a id=arrow href=#end><i style="opacity:0.3;font-size:80px !important;" class="fas fa-angle-down"></i></a></div> 
			<span id=end></span>

		</DIV></BODY>
		</html>
EOM;
	}

}
?>		  
