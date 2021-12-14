<?php 
//資料庫以及登入判定
abstract class db{
	
	protected	$db_host = "localhost";
	protected	$db_username = "root";
	protected	$db_password = "a12345678";
	protected	$user,$pass,$username,$userid;
	protected	$st_con,$at_con,$bt_con;
	
		function st_connect(){
			$this->st_con=mysqli_connect($this->db_host,$this->db_username,$this->db_password,"student");
		}
		function at_connect(){
			$this->at_con= mysqli_connect($this->db_host,$this->db_username,$this->db_password,"ateacher");
		}
		function bt_connect(){
			$this->bt_con= mysqli_connect($this->db_host,$this->db_username,$this->db_password,"bteacher");
		}
	
		function login_yes(){
			echo "<script  language=javascript>
			alert('登入成功');
			location.href='http://localhost/%E5%B0%88%E9%A1%8C3.0/at/index.php?page=1';
			</script>";
		}
		function login_no(){
			echo "<script  language=javascript>
			alert('登入失敗');
			location.href='http://localhost/%E5%B0%88%E9%A1%8C3.0/index/';
			</script>";
		}
		function check_user(){
			session_start();
			if(is_null($_SESSION["at_user"]))
			{
			$this->login_no();
			}
		}
		
}
//登入
class login extends db{
		function __construct($user,$pass){
			$this->at_connect();
			$this->user = $user; 
			$this->pass = $pass; 
			$this->select();
		}
		function select(){
			$sql_login="SELECT * FROM `teacher`,`name_teacher` where teacher.aid=name_teacher.aid AND user='$this->user' AND pass='$this->pass'";
			$row_result=mysqli_fetch_assoc(mysqli_query($this->at_con,$sql_login));
			if(empty($row_result))
    		{  
			$this->login_no();		
    		}  
    		else  
    		{  
			$this->username = $row_result["name"];
			$this->userid = $row_result["aid"];
			$this->session();
    		} 
			
		}
		function session(){
			session_start();
			$_SESSION["at_user"] = $this->user;
			$_SESSION["at_pass"] = $this->pass;
			$_SESSION["at_username"] = $this->username;
			$_SESSION["at_userid"] = $this->userid;
			$this->login_yes();

		}
	}
class at extends db{
	private $page_name;
	function __construct($page_name){
			$this->check_user();
			$this->page_name = $page_name;
			$this->nav();
		}
	function nav(){
	echo "
	<nav class='navbar fixed-top navbar-expand-lg navbar-dark bg-dark fixed-top'>
    <div class='container'>
      <a class='navbar-brand' href='index.php?page=1'>嶺東推廣中心-教師</a>
      <button class='navbar-toggler navbar-toggler-right' type='button' data-toggle='collapse' data-target='#navbarResponsive' aria-controls='navbarResponsive' aria-expanded='false' aria-label='Toggle navigation'>
      <span class='navbar-toggler-icon'></span>
      </button>
    <div class='collapse navbar-collapse' id='navbarResponsive'>
       <ul class='navbar-nav ml-auto'>
         <li class='nav-item active'>
           <a class='nav-link' href='index.php?page=1'>主頁</a>
         </li>
       <li class='nav-item dropdown'>
         <a class='nav-link dropdown-toggle' href='#' id='navbarDropdownPortfolio' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
              課程</a>
           <div class='dropdown-menu dropdown-menu-right' aria-labelledby='navbarDropdownPortfolio'>
              <a class='dropdown-item' href='class.php'>我的班級</a>
              <a class='dropdown-item' href='bb.php'>我的討論</a>
			  <a class='dropdown-item' href='slect.php'>查詢課堂</a>
              <a class='dropdown-item' href='newclass.php'>開設班級</a>
              <a class='dropdown-item' href='need.php'>查看學生請願</a>
          </div>
        </li>
          <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdownPortfolio' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
              成績</a>
            <div class='dropdown-menu dropdown-menu-right' aria-labelledby='navbarDropdownPortfolio'>
            <a class='dropdown-item' href='nember.php'>設定成績分配</a>
            <a class='dropdown-item' href='nem.php'>評分</a>
            </div>
          </li>
		  <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdownBlog' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
            ".$_SESSION['at_username']."</a>
           <div class='dropdown-menu dropdown-menu-right' aria-labelledby='navbarDropdownBlog'>
            <a class='dropdown-item' href='loginout.php'>登出</a>
       	   </div>
       	  </li>
    </ul>
    </div>
    </div>
  	</nav>
  <div class='container'>
  <h1 class='mt-4 mb-3'><small></small></h1>
  <ol class='breadcrumb'>
  <li class='breadcrumb-item'><a href=''>教師</a></li>
  <li class='breadcrumb-item active'>".$this->page_name."</li></ol>";
		}
}
class index extends db{
	//placard//
	protected $pl_length='6';
	protected $pl_arrtot;
	protected $prevpage;
	protected $nextpage;
	//sql//
	protected $sql_pl_count;
	protected $sql_pl;
	protected $sql_hot_class;
	protected $sql_need_class;
	
		function __construct(){
			$this->at_connect();
			$this->bt_connect();
			$this->data();	
		}
		function data(){
			$this->sql_pl_count = "SELECT * FROM `placard`";
			$this->sql_hot_class = "SELECT * FROM `teacher`,`name_teacher` INNER JOIN `classroom` WHERE classroom.status='已通過' ORDER BY `classroom`.`number` DESC LIMIT 0,4";
			$this->sql_need_class = "SELECT * FROM `class_need` ORDER BY `hot` DESC LIMIT 0,4";
			$this->pl_arrtot = mysqli_num_rows(mysqli_query($this->bt_con,$this->sql_pl_count));
		}
		function placard($page){
			$pagenum=$page;
			$pagetot=ceil($this->pl_arrtot/$this->pl_length);
			if($pagenum>=$pagetot){
			$pagenum=$pagetot;
			}	
			$offset=($pagenum-1)*$this->pl_length;
			$this->sql_pl="select * from placard order by date DESC limit {$offset},{$this->pl_length}";  
			$this->prevpage=$pagenum-1;
			$this->nextpage=$pagenum+1;
			
			$result = mysqli_query($this->bt_con,$this->sql_pl);
			while($row_result=mysqli_fetch_assoc($result)){
			echo 
			"<tr>
			<td><a href='placard.php?id=".$row_result['placard_id']."'>".$row_result['theme']."</td>
			<td>".$row_result['date']."</td>
			</tr>";
			}
		}
		function placard_page($page){
			$num_rows = mysqli_num_rows(mysqli_query($this->bt_con,$this->sql_pl));
			$ii=ceil($num_rows/$this->pl_length+1);
			echo "<li> <a href='index.php?page={$this->prevpage}'>&laquo;</a></li>";
				for ( $i=1 ; $i<=$ii ; $i++ )	
				{
				echo  "<li><a href='index.php?page={$i}'>$i</a></li>";
				}
			echo "<li><a href='index.php?page={$this->nextpage}'>&raquo;</a></li>";
		}
}
class my_select extends db{
	//sql//
	protected $sql_my_select;	
	
		function __construct(){
			$this->at_connect();
			$this->data();
			$this->select();
		}
		function data(){
			$bb=$_GET["priority"];
			$cl=$_GET["class"];
	  		$te=$_GET["te"];
	  		$da=$_GET["dat"];
				if($_GET["priority"]=='1'){
					$vi="status='已通過'";
					}elseif($_GET["priority"]=='2'){
						$vi="status='已通過' AND class_name='$cl'";
					}elseif($_GET["priority"]=='3'){
						$vi="status='已通過' AND name='$te'";
					}elseif($_GET["priority"]=='4'){
						$vi="status='已通過' AND week='$da'";
					}
			$this->sql_my_select = "SELECT * FROM `classroom`,`name_teacher` WHERE $vi  AND classroom.aid=name_teacher.aid";
		}
		function select(){
			$result = mysqli_query($this->at_con,$this->sql_my_select);
			while($row_result=mysqli_fetch_assoc($result)){
			echo 
			"<div class='col-lg-4 mb-4'>
        		<div class='card h-100'>
          			<h4 class='card-header'>".$row_result["class_name"]."/星期".$row_result["week"].'/第'."".$row_result["time"].'節'.'-第'.$row_result["time_up"]."</h4>
          		<div class='card-body'>
				<p class='card-text'>上課地點:".$row_result["location"]."</p>
            	<p class='card-text'>".nl2br($row_result['Introduction'])."</p>
				</div>
          <div class='card-footer'>
		  		<a href='#' class='btn btn-warning'>指導老師:".$row_result["name"]."</a>
		</div>
        		</div>
      	</div>";
		}
		}  
}
class class_need extends db{
	//sql//
	protected $sql_class_need_select;
	
		function __construct(){
			$this->at_connect();
			$this->data();
		}
		function data(){
			$this->sql_class_need_select = "SELECT * FROM `class_need` ORDER BY `hot` DESC";
		}
		function select(){
			$result = mysqli_query($this->at_con,$this->sql_class_need_select);
			while($row_result=mysqli_fetch_assoc($result)){
			echo 
			"<div class='col-lg-4 mb-4'>
			<div class='card h-100 text-center' ><img class='card-img-top' src='img/7.png' alt=''>
          		<h4 class='card-header'>".$row_result["class_name"]."</h4>
          	<div class='card-body'>
				<p class='card-text'>".nl2br($row_result['Introduction'])."</p>
			</div>
          	<div class='card-footer'>
            	<a href='#' class='btn btn-danger'>熱度".$row_result["hot"]."</a>
          	</div>
        	</div>
      		</div>";
			}
		}
}
class at_talk extends db{
	//sql//
	protected $sql_at_nowclass;
	protected $sql_at_closeclass;
	protected $sql_at_check;
	protected $sql_at_title;
	protected $sql_at_talk;
	protected $sql_at_theme;
	protected $sql_at_RE_theme;
	protected $sql_at_droplist;
	
	
		function __construct(){
			$this->at_connect();
			$this->st_connect();
			$this->data();
		}
		function data(){
			if(!empty($_GET['id'])){
				$this->sql_at_check = "SELECT * FROM `classroom` WHERE `class_id`='".$_GET['id']."' AND `teacher`='".$_SESSION["at_username"]."'";
				$this->sql_at_title="SELECT * FROM `classroom` WHERE `class_id`='".$_GET['id']."'";
				$this->sql_at_talk="SELECT * FROM mengess,classroom WHERE mengess.class_id = '".$_GET['id']."'AND classroom.class_id='".$_GET['id']."'";
				$this->check();
			}
			if(!empty($_GET['me_id'])){
				$this->sql_at_theme = "SELECT * FROM `mengess` WHERE mengess_id='".$_GET['me_id']."'";
				$this->sql_at_RE_theme = "SELECT * FROM `re_mengess` WHERE `mengess_id`='".$_GET['me_id']."'";
			}
			$this->sql_at_nowclass = "SELECT * FROM `classroom`,`name_teacher` WHERE name_teacher.name='".$_SESSION["at_username"]."' AND classroom.status='已通過' AND classroom.aid=name_teacher.aid";
			$this->sql_at_closeclass ="SELECT * FROM `classroom`,`name_teacher` WHERE name_teacher.name='".$_SESSION["at_username"]."' AND classroom.status='已結束' AND classroom.aid=name_teacher.aid";
			$this->sql_at_droplist = "SELECT * FROM `classroom` WHERE teacher ='".$_SESSION["at_username"]."' AND status='已通過'";
			
			
		}
		function check(){
			$result = mysqli_query($this->at_con,$this->sql_at_check);
			$row_result=mysqli_fetch_assoc($result);
			if($row_result['teacher']!=$_SESSION["at_username"]){
			echo 
			"<script  language=javascript>
			location.href='index.php?page=1';
			</script>";
			}	
		}
		function title(){
			$result = mysqli_query($this->at_con,$this->sql_at_title);
			$row_result=mysqli_fetch_assoc($result);
			echo $row_result["class_name"];
		}
		function select_nowclass(){
			$result = mysqli_query($this->at_con,$this->sql_at_nowclass);
			if($_SERVER['PHP_SELF']=='/專題3.0/at/class2.php' or $_SERVER['PHP_SELF']=='/專題3.0/at/class.php'){
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='class2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
				}else if($_SERVER['PHP_SELF']=='/專題3.0/at/bb2.php' or $_SERVER['PHP_SELF']=='/專題3.0/at/bb.php'){
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='bb2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
				}else if($_SERVER['PHP_SELF']=='/專題3.0/at/nem2.php' or $_SERVER['PHP_SELF']=='/專題3.0/at/nem.php'){
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='nem2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
				}
		}
		
		function select_closeclass(){
			$result = mysqli_query($this->at_con,$this->sql_at_closeclass);
			if($_SERVER['PHP_SELF']=='/專題3.0/at/class2.php' or $_SERVER['PHP_SELF']=='/專題3.0/at/class.php'){
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='class2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
				}else if($_SERVER['PHP_SELF']=='/專題3.0/at/bb2.php' or $_SERVER['PHP_SELF']=='/專題3.0/at/bb.php'){
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='bb2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
				}
				else if($_SERVER['PHP_SELF']=='/專題3.0/at/nem2.php' or $_SERVER['PHP_SELF']=='/專題3.0/at/nem.php'){
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='nem2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
				}
		}
		function talk(){
			$result = mysqli_query($this->at_con,$this->sql_at_talk);
			while($row_result=mysqli_fetch_assoc($result)){
			echo"
		 	<div class='card mb-4'>
				<div class='card-body'>
            	<h2 class='card-title'>".$row_result["title"]."(".$row_result["pepole"].")</h2>
            	<p class='card-text'>".$row_result["text"]."</p>
            	<a href='bb3.php?me_id=".$row_result["mengess_id"]."&id=".$row_result["class_id"]."' class='btn btn-primary'>查看討論</a>&emsp;<a href='bb21.php?id=".$_GET['id']."&me_id=".$row_result["mengess_id"]."'button class='btn btn-danger' type='button'>刪除</button></a>
          		</div>
          	<div class='card-footer text-muted'>".$row_result["writer_username"]."</div> 
		  	</div>"
				;}
		}
		function title_talk(){
			$result = mysqli_query($this->at_con,$this->sql_at_theme);
			$row_result=mysqli_fetch_assoc($result);
			echo "
			<h2>標題:".$row_result['title']."</h2> 
			<p>作者:".$row_result['writer_username']."</p><hr><p>";
			echo nl2br($row_result['text']);
			echo "</p><hr>";
		}
		function Re_title_talk(){
			$result = mysqli_query($this->at_con,$this->sql_at_RE_theme);
			while($row_result=mysqli_fetch_assoc($result)){
			echo "
        	<div class='media mb-4'>
          	<img class='d-flex mr-3 rounded-circle' src='http://placehold.it/50x50' alt=''>
			<div class='media-body'>
		  	<hr>
            <h5 class='mt-0'>回覆者:".$row_result['writer_user']."<a href='de.php?re_id=".$row_result["re_id"]."&me_id=".$_GET['me_id']."&id=".$_GET['id']."'button class='btn btn-danger' type='button'>刪除</button></a></h5>";
			echo nl2br($row_result['text']);
			echo"
          	</div>
        	</div>
			<hr>";
			}
		}
		function out_re(){
			$sql ="DELETE FROM re_mengess WHERE re_id='".$_GET['re_id']."'";
			$result = mysqli_query($this->at_con,$sql);
			$sql ="UPDATE mengess SET pepole=pepole-1 WHERE mengess_id='".$_GET['me_id']."'";
			$result = mysqli_query($this->at_con,$sql);
		}
		function droplist(){
			$result = mysqli_query($this->at_con,$this->sql_at_droplist);
			while($row_result=mysqli_fetch_assoc($result)){
			echo "<option value='".$row_result['class_id']."'>".$row_result['class_name']."</option>";
			}
		}
		function new_talk(){
			$n=$_POST['n0'];
			$n1=$_POST['n1'];
			$n2=$_POST['n2'];
			$n3=$_POST['n3'];
			$sql ="INSERT INTO `mengess` (`class_id`,`title`, `writer_user`, `text`,`writer_identity`,`writer_username`) VALUES ('$n','$n1','$n2','$n3','ateacher','".$_SESSION['st_username']."')";
			mysqli_query($this->at_con, $sql);
		}
		function Re_talk(){
			$ss=$_POST['hi'];
			$id=$_POST['h2'];
			$sql ="INSERT INTO `re_mengess` (`writer_user`, `text`,";
			$sql.="`mengess_id`,`writer_id`,`writer_identity`) VALUES ('";
			$sql.=$_POST["h3"]."','".$_POST["hi"]."','";
 			$sql.=$_POST["h2"]."','".$_SESSION['st_user']."','atecher')";
			mysqli_query($this->at_con, $sql);
			$sql2 ="UPDATE mengess SET `pepole`=`pepole`+1 WHERE mengess_id=$id";
			mysqli_query($this->at_con, $sql2);
		}

}
class at_nem extends db{
	//sql//
	protected $sql_select_nem;
	
		function __construct(){
			$this->st_connect();
			$this->data();
		}
		function data(){
			 $this->sql_select_nem= "SELECT * FROM `class`,`st_data`,`student` WHERE class_id='".$_GET['id']."' AND class.user=student.user AND st_data.sid=student.sid";
		}
		function select(){
			$result = mysqli_query($this->st_con,$this->sql_select_nem);
			while($row_result=mysqli_fetch_assoc($result)){
			echo "<tr>
			<td>".$row_result["username"]."</td>
			<td>".$row_result["sex"]."</td>
			<td>".$row_result["mail"]."</td>
			<td>".$row_result["phone"]."</td>
			<td>".$row_result["phone-house"]."</td>
			</tr>";
				}
			}
		}
class check extends db{
//sql//
	protected $sql_de_mengess;
	protected $sql_de_re_mengess;
	
		function __construct(){
			$this->at_connect();
			$this->data();
			$this->delete();
		}
		function data(){
			 $this->sql_de_mengess= "DELETE FROM mengess WHERE  mengess_id='".$_GET['me_id']."'";
			 $this->sql_de_re_mengess= "DELETE FROM re_mengess WHERE  mengess_id='".$_GET['me_id']."'";
		}
		function delete(){
			$result = mysqli_query($this->at_con,$this->sql_de_mengess);
			$result = mysqli_query($this->at_con,$this->sql_de_re_mengess);
			echo "<script  language=javascript>
			location.href='bb2.php?id=".$_GET['id']."';
			</script>";
		}
}
class placard extends db{
	//sql//
	protected $sql_placard;
	
		function __construct(){
			$this->bt_connect();
			$this->data();
			$this->select();
		}
		function data(){
			$this->sql_placard="SELECT * FROM `placard`,`teacher` WHERE placard_id ='".$_GET['id']."'AND teacher.bid=placard.bid";
		}
		function select(){
			$result = mysqli_query($this->bt_con,$this->sql_placard);
			$row_result=mysqli_fetch_assoc($result);
			echo "<h2>標題:".$row_result['theme']."</h2> 
			<p>作者:".$row_result['username']."</p><hr><p>";  
			echo nl2br($row_result['content']);
		}
}
class nem extends db{
	//sql//
	protected $sql_nem_select;
	
		function __construct(){
			$this->st_connect();
			$this->data();
			$this->select();
		}
		function data(){
			$this->sql_nem_select="SELECT * FROM student.student,student.number,ateacher.classroom WHERE student.number.class_id='".$_GET['id']."' AND ateacher.classroom.class_id='".$_GET['id']."'AND student.number.user=student.student.user";
		}
		function select(){
			ini_set('display_errors','off');
			$result = mysqli_query($this->st_con,$this->sql_nem_select);
			$row_result=mysqli_fetch_assoc($result);
			echo "<td>".$row_result["username"]."</td>
			<td>".$row_result["st_usually"]."</td>
			<td>".$row_result["st_mid"]."</td>
			<td>".$row_result["st_last"]."</td>
			<td>".$row_result["st_total"]."</td>";
			if(!empty($row_result["username"])){
			echo "<td><a href='nem4.php?num_id=".$row_result["num_id"]."&id=".$_GET['id']."' class='btn btn-danger'>評分</a></td>";  
			}
		}
}
class insert_nem extends db{
	//sql//
	protected $sql_nem_select;
	
		function __construct(){
			$this->at_connect();
			$this->st_connect();
			$this->data();
			$this->select();
		}
		function data(){
			$this->sql_nem_select="SELECT usually_test,mid_test,last_test FROM `classroom` WHERE class_id='".$_GET['id']."'";
		}
		function select(){
			$result = mysqli_query($this->at_con,$this->sql_nem_select);
			$row_result=mysqli_fetch_assoc($result);
			if(empty($row_result['usually_test'])or empty($row_result['mid_test']) or empty($row_result['last_test'])){
			echo "<script  language=javascript>
			alert('請先設定課程標準');
			location.href='nem2.php?id=".$_GET['id']."';
			</script>";	
			}else{
			$sql="UPDATE `number` SET `st_mid` = '".$_GET['mid']."', `st_last` = '".$_GET['last']."', `st_usually` = '".$_GET['uauslly']."' WHERE `number`.`num_id` = ".$_GET['num_id'].";";
			$result = mysqli_query($this->st_con,$sql);
			$sql="UPDATE student.number,ateacher.classroom set student.number.st_total =((student.number.st_mid/100*ateacher.classroom.mid_test)+(student.number.st_last/100*ateacher.classroom.last_test)+(student.number.st_usually/100*ateacher.classroom.usually_test)) WHERE student.number.num_id='".$_GET['num_id']."' AND ateacher.classroom.class_id='".$_GET['id']."'";
			$result = mysqli_query($this->st_con,$sql);
			$sql="UPDATE class set status='已結束' where class_id='".$_GET['id']."'";
			$result = mysqli_query($this->st_con,$sql);
			echo "<script  language=javascript>
			alert('已完成');
			location.href='nem2.php?id=".$_GET['id']."';
			</script>";	
			}
		}
		function insert(){
			ini_set('display_errors','off');
			$result = mysqli_query($this->st_con,$this->sql_nem_select);
			$row_result=mysqli_fetch_assoc($result);
			echo "<td>".$row_result["username"]."</td>
			<td>".$row_result["st_usually"]."</td>
			<td>".$row_result["st_mid"]."</td>
			<td>".$row_result["st_last"]."</td>
			<td>".$row_result["st_total"]."</td>
			<td><a href='nem4.php?num_id=".$row_result["num_id"]."&id=".$_GET['id']."' class='btn btn-danger'>評分</a></td>";  
		}
}	
class nember extends db{
	//sql//
	protected $sql_nem_select;
	protected $sql_nem_insert;
	
		function __construct(){
			$this->at_connect();
			$this->st_connect();
			$this->data();
			$this->select();
		}
		function data(){
			$this->sql_nem_select="SELECT * FROM `classroom` WHERE teacher='".$_SESSION['at_username']."'";
		}
		function select(){
		$result = mysqli_query($this->at_con,$this->sql_nem_select);
		while($row_result=mysqli_fetch_assoc($result)){
		echo "
		<div class='col-lg-4 mb-4'>
        <div class='card h-100'>
          <h4 class='card-header'>".$row_result["class_name"]."/星期".$row_result["week"].'/第'."".$row_result["time"].'節'.'-第'.$row_result["time_up"].'節'."</h4>
          <div class='card-body'>
            <p class='card-text'>平時成績:".$row_result["usually_test"]."%&nbsp;期中考:".$row_result["mid_test"]."%&nbsp;期末考:".$row_result["last_test"]."%</p>
          </div>
          <div class='card-footer'>
		  <a href='nember2.php?id=".$row_result["class_id"]."' class='btn btn-warning'>配置分數</a>
          </div>
       </div>
       </div>";	
		}
		}
		function insert(){
			$id=$_GET["a4"];
			$w1=$_GET["a1"];
			$w2=$_GET["a2"];
			$w3=$_GET["a3"];
			$w4=$w1+$w2+$w3;
			if($w4!=100){
			echo "<script  language=javascript>
			alert('請輸入加總為100的值目前你輸入的是$w4');
			location.href='nember.php';
			</script>";
			}else{
			$this->sql_nem_insert="UPDATE `classroom` SET `usually_test` = '$w1', `mid_test` = '$w2', `last_test` = '$w3' WHERE `classroom`.`class_id` = '$id';";
			$result = mysqli_query($this->at_con,$this->sql_nem_insert);
			echo "<script  language=javascript>
			alert('以設定完成');
			location.href='nember.php';
			</script>";
			}
		}
}
class new_class extends db{
	//sql//
	protected $sql_class_insert;
	
		function __construct(){
			$this->at_connect();
			$this->data();
			$this->insert();
		}
		function data(){
			$this->sql_class_insert="INSERT INTO `classroom` (`class_name`, `week`, `time`, `time_up`, `Introduction`, `location`,`plan`, `teacher`) VALUES ('".$_GET['class_name']."','".$_GET['week']."', '".$_GET['time']."', ".$_GET['time_up'].", '".$_GET['introduction']."', '".$_GET['location']."', '".$_GET['plan']."', '".$_SESSION['at_username']."');";
		}
		function insert(){
			$result = mysqli_query($this->at_con,$this->sql_class_insert);
			echo "<script  language=javascript>
			alert('以設定完成');
			location.href='newclass.php';
			</script>";
		}
}	
?>