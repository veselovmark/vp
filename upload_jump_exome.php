<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
$file_code =  $_GET["file_code"];		//获取到文件名（来自index的form标签）
 ?>
<script language="javascript" type="text/javascript">
setTimeout("javascript:location.href='choose_data.php?file_code=<?php echo $file_code ?>'", 3000); 
</script>
<html xmlns="http://www.w3.org/1999/xhtml">
<!--<meta http-equiv="refresh" content="15"; url="http://localhost/pgi_test/search_drug_response.php?code=<?php echo $file_code; ?>">-->
<?php
	
//include ("phpmail_jiucool.php");								//加载mail的功能
include "mysql_connect.php";			

$file = "ftp/".$file_code."/".$file_code.".txt";			
//$fcount=fopen("upload_file/tmp_data/count.xls","a+");		//打开统计文件
//$SNP_count=0;
//$drug_count=0;
$tmp="";
// read the file

$file_list = file_get_contents($file);							//用file_get_contents()获取txt文件的全部字符串


$file_list = preg_replace("/\s\d*\s\d*\s/","t",$file_list);		//将有“空字符+任意数字+空字符+任意数字+空字符”的字符串 用t来替代




$file_drug = $file.".drug.xls";
$fh = fopen($file_drug, "a");			//写入方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。
$file_mybionet = $file.".Mybionet.xml";
$fm = fopen($file_mybionet,"a");
$file_Cytoscape = $file.".Cytoscape.csv";	//csv逗号分隔文件
$fc = fopen($file_Cytoscape,"a");
$file_Attribute = $file.".Cytoscape.Attribute.csv";
$fa = fopen($file_Attribute,"a");




$sql = "CREATE TABLE $file_code
(
RSID VARCHAR(20),
Alleles VARCHAR(7),
Gene VARCHAR(50),
Drug VARCHAR(60),
System VARCHAR(60),
subSystem TEXT,
drugID VARCHAR(60),
info TEXT,
Level VARCHAR(10),
Efficacy VARCHAR(20),
Dosage VARCHAR(20),
Toxicity VARCHAR(20),
Genotype VARCHAR(10),
Sentence TEXT
)";
$result = mysql_query($sql);// or die (mysql_error());

$sql1 = "select * from test";
$result1 = mysql_query($sql1);

$flag = 0;

//mysql_close($conn);
$head_my = '<graph name="Mybionet Network" type="directed">';//用于在xml文档中划出图片头和图片尾，将设计好的船到另一个网站，就可以画出关系图
$tail_my = "\n</graph>";
fwrite($fm, $head_my);

while($row = mysql_fetch_array($result1))		//获取Drug_Response_Drugs 数据表中的每一个字段
{
$rs_flag=0;
$RSID = $row['RSID'];
$Gene = $row['Gene'];
$Drug = $row['Drug'];
$Alleles = $row['Alleles'];
$Level = $row['Level'];
$Efficacy = $row['Efficacy'];
$Dosage = $row['Dosage'];
$Toxicity = $row['Toxicity'];
$Genotype = $row['Genotype'];
$Notes = $row['Sentence'];
$System=$row['System'];
$subSystem=$row['subSystem'];
$drugID=$row['drugID'];
$info=$row['info'];

$RSID_search = $RSID."t".$Genotype;
$RSID_index = strpos($file_list,$RSID_search);		//strpos(string,find,start) string：字典；find：要查的字符；start：开始查询的位置（默认为文件开头）.返回第一个字符串所在的位置，如果没有对应字符串，则返回false
//echo $file_list;
if($RSID_index !== false){							//只有当检索到字符串时才能执行if语句
        $RSID_ok = $RSID;
        $rs_flag = 1;
		//		$SNP_count++;
		if($tmp!=$Drug)
		{
		//	$drug_count++;
			$tmp=$Drug;
		}
#	echo "$RSID<br/>";
}

#echo "$RSID<br/>";
if($rs_flag == 1){

$stri = $RSID."\t".$Alleles."\t".$Gene."\t".$Drug."\t".$Level."\t".$Efficacy."\t".$Dosage."\t".$Toxicity."\t".$Genotype."\t".$Notes."\n";	
$s_1="INSERT INTO $file_code (RSID,Alleles,Gene,Drug,System,subSystem,drugID,info,Level,Efficacy,Dosage,Toxicity,Genotype,Sentence) VALUES 
('$RSID',
'$Alleles',
'$Gene',
'$Drug',
'$System',
'$subSystem',
'$drugID',
'$info',
'$Level',
'$Efficacy',
'$Dosage',
'$Toxicity',
'$Genotype',
'$Notes')";
$result_1=mysql_query($s_1);//  or die ("错误：$s_1");	//把从数据库中找到的信息，直接复制到用制表符\t 放入到excel 表格中
fwrite($fh, $stri);
$Drug1=str_replace(array("\""),"",$Drug);
$Gene1=str_replace(array("\""),"",$Gene);
$RSID1=str_replace(array("\""),"",$RSID);
$str_node_drug = "\n<Node id=\"$Drug1\" type=\"complex\" name=\"$Drug1\" desc=\"\" subcellular=\"Extracellular\" link=\"\"/>";		//编写xml文件信息(编写xml文件的目的是什么啊)
$str_node_gene = "\n<Node id=\"$Gene1\" type=\"DNA\" name=\"$Gene1\" desc=\"\" subcellular=\"Innercellular\" link=\"\"/>";			
$str_node_rs   = "\n<Node id=\"$RSID1\" type=\"SNP\" name=\"$RSID1\" desc=\"\" subcellular=\"\" link=\"\"/>";
$str_edge_rs   = "\n<Edge fromID=\"$RSID1\" toID=\"$Gene1\" type=\"positive\" size=\"\" desc=\"\"/>";
$str_edge_gene   = "\n<Edge fromID=\"$Gene1\" toID=\"$Drug1\" type=\"positive\" size=\"\" desc=\"\"/>";


fwrite($fm, $str_node_drug);
fwrite($fm, $str_node_gene);
fwrite($fm, $str_node_rs);
fwrite($fm, $str_edge_rs);
fwrite($fm, $str_edge_gene);



$strc_gene = $Gene."\t".$Drug."\t"."pp\n"; 
$strc_rs = $RSID."\t".$Gene."\tpp\n";
fwrite($fc,$strc_rs);
fwrite($fc,$strc_gene);

$str_rs_a = $RSID."\t0\n";
$str_gene_a = $Gene."\t1\n";
$str_drug_a = $Drug."\t2\n";
fwrite($fa,$str_rs_a);
fwrite($fa,$str_gene_a);
fwrite($fa,$str_drug_a);
}
}
//fwrite($fcount,$SNP_count."\t".$drug_count."\n");
//fclose($fcount);
fwrite($fm, $tail_my);
fclose($fh);
fclose($fm);
fclose($fa);
fclose($fc);

?>

<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php include("title.php") ?>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="default.css" rel="stylesheet" type="text/css" media="screen" /><!--导入css链接-->
<link rel="stylesheet" href="css/components/style.css" />
<link rel="stylesheet" href="css/components/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />
</head>
<body>
<!-- start header -->
<?php 
include("head.php");
?>
<!-- end header -->


<!-- start menu -->
<?php include("navbar.php"); ?>
<!-- end menu -->


<!-- start page -->
<div id="page">
	<!-- start content -->
	<div id="content">
		<h1 class="pagetitle">&nbsp;</h1>
		<a href="#" id="rss-posts"></a>
		
		<div class="post">
			<h2 class="title">Your results are ready</h2>
			<!--<p class="byline"><small>Posted on August 25th, 2007 by <a href="#">admin</a> | <a href="#">Edit</a></small></p>-->
			<div class="entry">

				Your access code is <strong><font color="#FF0000"><?php echo $file_code?></font></strong>. You can check your results in the <a href="search.php?">Drug Response</a> page<br/>
				<br/>
        <p><strong>This page will jump to the result page automatically in <font color="#FF0000">3</font> seconds</strong></p>
       <!--         	<strong>We will also send you an e-mail at the same time!</strong>
					<br/>
					<br/>-->
		</div>
			<p class="meta"> &nbsp;&nbsp;&nbsp; <a href="#" class="comments">More Help </a></p>
		</div>
	</div>
	<!-- end content -->
	<!-- start sidebar -->
	<?php include 'sidebar.php'; ?>
	<!-- end sidebar -->
	<div style="clear: both;">&nbsp;</div>
</div>
<!-- end page -->
<?php 
include("foot.php");?>
</body>
</html>
