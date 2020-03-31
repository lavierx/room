<?php
//------------インクルード-----------------//
include("pre.php");
include("define.php");//サーバ定義
//配列をファイルから読み込み
$_set = unserialize(file_get_contents("_set{$_GET[k]}.dat"));//設定データ
$_setB = unserialize(file_get_contents("_setB{$_GET[k]}.dat"));//設定データ
$ph1_size=$_setB[icon_s];//顔写真アイコンサイズ 30px;
//配列をファイルから読み込み
$_entry = unserialize(file_get_contents("_entry{$_GET[k]}.dat"));//入室データ
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
	$logout = "login.php?url=".urlencode($url)."&logout=1";
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
	//echo $_GET[k];exit;	

  	//配列をファイルから読み込み
  	$_set = unserialize(file_get_contents("_set{$_GET[k]}.dat"));
  	$_setB = unserialize(file_get_contents("_setB{$_GET[k]}.dat"));
  	//print_r($_set);exit;

	//print_r($_POST[room]);
	foreach ($_POST[room] as $key => $value) {
		$_set[$key][room]=$_POST[room][$key];
		$_set[$key][capa]=$_POST[capa][$key];
	}
	$_setB[info]=$_POST[info];
	$_setB[icon_s]=$_POST[icon_s];
	$_setB[mochi]=$_POST[mochi];
	//print_r($_set);

	if($_POST[action]=="1"):
	  	//配列の中身をファイルに保存
	  	file_put_contents("_set{$_GET[k]}.dat", serialize($_set));
	  	file_put_contents("_setB{$_GET[k]}.dat", serialize($_setB));

	elseif($_POST[action]=="2")://リセット
		$_set="";//初期化
	  	file_put_contents("_set{$_GET[k]}.dat", serialize($_set));
		$_setB="";//初期化
	  	file_put_contents("_setB{$_GET[k]}.dat", serialize($_setB));
		$_entry="";//初期化
	  	file_put_contents("_entry{$_GET[k]}.dat", serialize($_entry));
  	endif;

	$url=str_replace("&set=1", "", $url);
  	echo "<script>location.href = '{$url}';</script>";

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
  	$_entry = unserialize(file_get_contents("_entry{$_GET[k]}.dat"));

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
  	file_put_contents("_entry{$_GET[k]}.dat", serialize($_entry));		
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
if($_GET[set]==''):
	$html=new showHtml();
	$html->html_h();
	$html->html_c($mid);
	//$html->html_r();
	$html->html_f();
endif;

//------------タイマー画面-----------------//
if($_GET[set]==2):
	$timer=new timer();
	//$timer->html_h();
	$timer->html_timer();
	//$html->html_r();
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

		global $ph1_size;

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
		<!--iframe高さ調整
		<script src="//vps.pia-no.com/js/jquery-iframe-auto-height-master/dist/iautoh_all.js"></script>	-->		

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
		  //$('{$_GET[k]}.datepicker'){$_GET[k]}.datepicker();
		  $('{$_GET[k]}.datepicker'){$_GET[k]}.datepicker({
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

    	  //$('.pushpin').pushpin();

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
		<!--<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js'></script>-->
		<!--<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/lity/1.6.6/lity.css' />  
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
		-->

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
		    _background:#37474f;
		    padding: 20px;
		    margin: -30px -7px -15px -7px;
		    color: silver !important;
		    border-radius: 10px;
			box-shadow: 2px 2px 4px inset;
		}
		.f2 {
		    background: rgba(255,255,255, 0.1);
		    background:#37474f;		    
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
	    	width:{$ph1_size}px;
	    	height:{$ph1_size}px;
		}
		.btn-large {
		    height: 54px;
		    line-height: 54px;
		    font-size: 22px;
		    padding: 0 28px;
		}
		.ic{$_GET[k]}{
			color:#f50057;
		}
		.time{
			color:aqua;
			font-size:19px;
			padding:0 10px;
			font-style:italic;
		}
		</style>	
		</head>
EOM;
	}

	//-----------htmlメインー-------------------------//

	public function html_c($mid){//メイン画面

		global $_set;
		global $_setB;
		global $user;
		global $NT2;
		global $url;		

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
		else://mimiIDあり
		
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
			    <div class="col s12">
					<div style="vertical-align:text-bottom">
						<iframe style="width:100%;height:75px" align="top" src="{$url}?&set=2" frameborder="0" scrolling="no"></iframe>
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
			<button onClick="sound()" class="btn waves-effect waves-light btn-large {$pulse}" type="submit" name="entry" value="{$value[room]}" style='margin:6px;'>{$value[room]}<i class="material-icons right">send</i></button>
EOM;
		}

		$html_r=$html->html_r();
		
		//メイン画面
		$url2=str_replace(".php", "", $_SERVER["SCRIPT_NAME"]);
		echo $html_c=<<<EOM
		<BODY><DIV class="container animated fadeIn">

			<nav class="cyan darken-2">
			<p style="font-weight:700;_text-shadow: #fff 0px 1px 2px, #000 0px -1px 1px;">
				<a style='display: inline-block;font-size:22px;' class=min href={$url}>部屋定員管理くん</a>
				<a style='display: inline-block' href={$url2}?k=1><i class="material-icons ic1">filter_1</i></a>
				<a style='display: inline-block' href={$url2}?k=2><i class="material-icons ic2">filter_2</i></a>
				<a style='display: inline-block' href={$url2}?k=3><i class="material-icons ic3">filter_3</i></a>
				<a style='display: inline-block' href={$url2}?k=4><i class="material-icons ic4">filter_4</i></a>
				<a style='display: inline-block' href={$url2}?k=5><i class="material-icons ic5">filter_5</i></a>
			</p>
			</nav>

			<DIV id=contents>

			{$name}

			<form action="{$url}" method="post" enctype="multipart/form-data" class=hoge>


			<!-- 音声ファイルの読み込み 9〜10行目にURLを指定 -->
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
			<p class='min _pushpin' style="margin-top:20px;font-size:27px;color:#006064;font-weight:700"><i class="material-icons">info</i>{$_setB[info]}</p>

			</DIV>
EOM;
	}

	//-----------部屋状況画面-------------------------//


	public function html_r(){//部屋状況画面

		global $_set;
		//print_r($_set);
		global $_entry;
		//print_r($_entry);

		//-----各自の演奏時間データ------//
		$timer=new timer();
		//$timer->html_h();
		$timer_all=$timer->html_timer_all("");// 1: 秒単位　それ以外:分単位
		//print_r($timer_all);//各自の演奏時間データ
		//echo $timer_all[1];
		
		foreach ($_set as $key => $value) {
			$person_s="";//初期化
			$cnt="";//初期化

			if($value[room]==""){continue;}
			foreach ($_entry[$value[room]] as $key2 => $value2) {
				list($name,$photo,$time)=explode("\t",$value2);
				//$time_df=time()-$time;

				//偏差値
				$sd=$timer->getSd($timer_all,$timer_all[$key2]);
				if($sd>65){
					$faa="faa-burst";
				}elseif($sd>=60){
					$faa="faa-flash";
				}elseif($sd>=50){
					$faa="faa-tada";
				}elseif($sd<50){
					$faa="faa-pulse";
				}

				$time_df=date("H:i",$time);
				$person_s.="<img onclick=\"M.toast({html: '{$name} <span class=\'time min _TEN\'>{$timer_all[$key2]}</span> {$time_df}'})\" class='ph1 {$faa} animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				//$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-burst animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				//$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-flash animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				//$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-tada animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				/*$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-tada animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-tada animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-tada animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";
				$person_s.="<img onclick=\"M.toast({html: '{$name} {$time_df}'})\" class='ph1 faa-tada animated _tooltipped _fadeIn _flipInX' src='{$photo}' title='{$name}' data-position='bottom' data-tooltip='{$name}'>";*/
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
				$def2_c="#eceff1";
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
		global $_setB;
		global $url;

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
		    background:#37474f;			
			background: linear-gradient(-45deg, rgba(108, 179, 255, .1), rgba(255, 255,255, .1)) fixed,
		  	url(img/lbg2.jpg);  		 
		 	background-size: cover;
			background-repeat: no-repeat;
			background-attachment: fixed;
		}
		#contents{
			background: none rgba(0,0,0,.3);
		    _background:#37474f;			
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

				        <div class="input-field col s12">
				          <textarea name=info id="textarea1" class="materialize-textarea">{$_setB[info]}</textarea>
				          <label for="textarea1">お知らせ</label>
				        </div>
						
						<button style="margin-left:-20px;" class="btn waves-effect waves-light btn-large pulse" type="submit" name="action" value=1>確定<i class="material-icons right">send</i></button>
						<a href="javascript:history.back();"><button style="margin-right:10px;" class="btn waves-effect waves-light btn-large blue-grey darken-2" type="button" name="" value=>戻る<!--<i class="material-icons right">send</i>--></button></a>
						<button class="btn waves-effect waves-light btn-large pink darken-3" type="submit" name="action" value=2>リセット<i class="material-icons right">send</i></button>
						<p style='color:pink'>リセット押すと、データが初期化されます</p>
					</div>

					<div class="row">
				    	<div class="col s1">
	      					<i class="material-icons">account_circle</i>
	      				</div>
				    	<div class="col s8">
				    		<p class="range-field">
	      					<input type="range" name="icon_s" min="30" max="100" value="{$_setB[icon_s]}" />
	      					</p>
	      				</div>
				    	<div class="col s3">	      				
	 	     				<input type="text" name="mochi" value="{$_setB[mochi]}" placeholder="持ち時間(分)" style="padding-left:5px"/>
	      				</div>
	      			</div>	

				</form>

				<p style='height:30px;'></p>

			</DIV>

		</DIV></BODY>
		</html>		
EOM;
	}

	//-----------htmlフッター-------------------------//

	public function html_f(){
		global $HP_DOMAIN;
		global $url;
		global $logout;

		if($_GET[k]==""){
			$url="{$url}?";	
		}
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
		            <p class="grey-text text-lighten-4 addr">　<i class="material-icons">build</i><a style='color:white;font-size:19px;' href={$url}&set=1 _data-lity>部屋設定</a></p>
		          </div>
		          <div class="col l4 offset-l2 s12">
		            <p class="white-text"><i class="fas fa-info fa-fw"></i>お問い合わせ</p>
		            <ul>
		              <li><a class="grey-text text-lighten-3" href="mailto:info@{$HP_DOMAIN}"><i class="fa fa-envelope"></i> info@{$HP_DOMAIN}</a></li>
		              <li><a class="grey-text text-lighten-3" href="index">DEMO</a></li>
		              <li><a class="grey-text text-lighten-3" href="{$logout}">LOGOUT</a></li>
		              		              
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

//============================================//
//---------------タイマー-------------------------//
//============================================//

if($_POST[ip_id]=="start")://開始

	//file_put_contents("debug.txt", $_POST[ip_id]);exit;

	// データベースに接続
	$dsn = "sqlite:time.sqlite";
	$conn = new PDO($dsn);
	 
	// テーブルの作成
	//$sql = "CREATE TABLE IF NOT EXISTS t1(id INTEGER PRIMARY KEY,mid,ymd,ts,te,tt)";
	//$stmt = $conn->prepare($sql);
	//$stmt->execute();
	 
	// データの追加
	$ymd=date("Ymd");
	$ts=time();
	//$xx=round($tt/60,1)."分演奏しました!";
	//echo "<script>alert('{$xx}');</script>";
	$sql = "INSERT INTO t1(mid,ymd,ts,te,tt) VALUES('{$mid}','{$ymd}','{$ts}','{$te}','{$tt}')";
	//file_put_contents("debug.txt", $sql);exit;
	$stmt = $conn->prepare($sql);
	$stmt->execute();

endif;

if($_POST[ip_id]=="stop")://停止

	// データベースに接続
	$dsn = "sqlite:time.sqlite";
	$conn = new PDO($dsn);
	$sql="
	SELECT *
	FROM t1
	WHERE mid = '{$mid}'
	ORDER BY id DESC 
	LIMIT 1
	;";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$id=$row[0];
		$ts=$row[3];		
	}

	//終了時間の書き込み
	$te=time();
	$tt=$te-$ts;

	$sql="
	UPDATE t1 SET
	te = '{$te}',
	tt = '{$tt}'
	WHERE id = '{$id}'
	;";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	
endif;

//継承クラス
class timer extends showHtml{

	//偏差値計算
	public function getSd($input,$value){
		//$input = array( 88, 32, 48, 95, 67, 45, 90 );
		//$value = 80;			 
		$sumsq = 0;
		$n = count($input);
		$m = array_sum($input) / $n;    // 平均			 
		foreach($input as $a){
		    $sumsq += pow(abs($a - $m), 2);
		}
		$v   = ($n >= 2) ? $sumsq / ($n - 1) : 0;    // 不偏分散
		$vp  = ($n > 0) ? $sumsq / $n : 0;           // 標本分散
		$sd  = sqrt($v);                            // 標本標準偏差
		$sdp = sqrt($vp);                           // 母標準偏差
		return $score = ($value - $m) / $sdp * 10 + 50;    // 偏差値
	}

	//グループ集計
	public function html_timer_all($sec){

	  	global $url;
	  	global $mid;

		$Ymd=date("Ymd");

		// データベースに接続
		$dsn = "sqlite:time.sqlite";
		$conn = new PDO($dsn);
		$sql = "SELECT mid,SUM(tt)
		FROM t1
		WHERE ymd ='{$Ymd}'
		GROUP BY mid
		ORDER BY SUM(tt) DESC	
		";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			if($sec=="1"){
				$time_all[$row[0]]=round($row[1]/1,1);
			}else{	
				$time_all[$row[0]]=round($row[1]/60,1);
			}
		}
		return $time_all; 
	 }//end:function

	 //タイマー記録
	 public function html_timer(){

	  	global $url;
	  	global $mid;
	  	global $_setB;
	  	global $FILE_DOMAIN2;

	  	//print_r($_setB);

		$Ymd=date("Ymd");

		// データベースに接続
		$dsn = "sqlite:time.sqlite";
		$conn = new PDO($dsn);
		/*$sql = "SELECT mid,SUM(tt)
		FROM t1
		WHERE ymd =".$XXXX."
		GROUP BY mid
		ORDER BY SUM(tt) DESC	
		";*/

		$sql="
		SELECT SUM(tt)
		FROM t1
		WHERE mid = '{$mid}'
		AND  ymd = '{$Ymd}'
		LIMIT 1
		;";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$time_sum=round($row[0]/60,1);
		}

		//持ち時間のこり
		if($_setB[mochi]==""){
			//$_setB[mochi]="12";
			$time_zan_s="";
		}else{
			$time_zan=$_setB[mochi]-$time_sum;
			$time_zan_s="残:{$time_zan}";
		}

	    echo $html_timer=<<<EOM
		<script>
		$(document).ready(function(){
		  var sec = 0;
		  var min = 0;
		  var hour = 0;

		  var timer;

		  // スタート
		  $('#start').click(function() {

		  	sound2();//音

		    // 00:00:00から開始
		    sec = 0;
		    min = 0;
		    hour = 0;
		    $('#clock').html('00:00:00');
		    timer = setInterval(countup, 1000);

		    $(this).prop('disabled', true);
		    $('#stop,#reset').prop('disabled', false);

			//id取得
			var id = $(this).attr("id"); 
			var val = $(this).val();
			//alert(id);
			//POST送信
			var postData = {"ip_id":id, "ip_val":val};
			$.post("{$url}",postData);

		  });

		  // ストップ
		  $('#stop').click(function() {
		  	
		  	//sound3();//音

		    // 一時停止
		    clearInterval(timer);

		    $(this).prop('disabled', true);
		    $('#restart').prop('disabled', false);
		    $('#start').prop('disabled', false);	    

			//id取得
			var id = $(this).attr("id"); 
			var val = $(this).val();
			//alert(id);
			//POST送信
			var postData = {"ip_id":id, "ip_val":val};
			$.post("{$url}",postData);

			location.href='{$url}?&set=2';
		  });

		  // リスタート
		  $('#restart').click(function() {
		    // 一時停止から再開
		    timer = setInterval(countup, 1000);

		    $(this).prop('disabled', true);
		    $('#stop').prop('disabled', false);
		  });

		  // リセット
		  $('#reset').click(function() {
		    // 初期状態
		    sec = 0;
		    min = 0;
		    hour = 0;
		    $('#clock').html('00:00:00');
		    clearInterval(timer);

		    $('#stop,#restart,#reset').prop('disabled', true);
		    $('#start').prop('disabled', false);
		  });

		 /**
		  * カウントアップ
		  */
		  function countup()
		  {
		    sec += 1;

		    if (sec > 59) {
		      sec = 0;
		      min += 1;
		    }

		    if (min > 59) {
		      min = 0;
		      hour += 1;
		    }

		    // 0埋め
		    sec_number = ('0' + sec).slice(-2);
		    min_number = ('0' + min).slice(-2);
		    hour_number = ('0' + hour).slice(-2);

		    $('#clock').html(hour_number + ':' +  min_number + ':' + sec_number);
		  }
		});
		</script>
		<style>
			input{
				font-size:20px;
			}
			.odometer{
				font-size:22px;
				padding-top:5px;			
			}
		</style>

		<!-- 音声ファイルの読み込み -->
		<audio id="sound-file2" preload="auto">
			<source src="2.mp3" type="audio/mp3">
		</audio>
		<script>
		function sound2(){
		// [ID:sound-file]の音声ファイルを再生[play()]する
		document.getElementById( 'sound-file2' ).play() ;}
		</script>

		<!--表示-->
		<div id="clock">00:00:00</div>
		<form style="float:left;margin-right:10px;">
			<input type="button" id="start" class=ip value="Start">
			<input type="button" id="stop" value="Stop" class=ip disabled>
			<!--<input type="button" id="restart" value="Restart" disabled>
			<input type="button" id="reset" value="Reset" disabled>-->		
		</form>

		<!--持ち時間消費-->
		<script src="{$FILE_DOMAIN2}/js/odometer-master/odometer.js"></script>
		<link rel="stylesheet" href="{$FILE_DOMAIN2}/js/odometer-master/themes/odometer-theme-train-station.css" />	
		<div class='odometer'>0</div>
		<script>
		  setTimeout(function(){
		    $('.odometer').html('{$time_sum}');
		  }, 1000);
		</script>

		{$time_zan_s}

EOM;

	  }//end:function
 
}//end:class

?>		  
