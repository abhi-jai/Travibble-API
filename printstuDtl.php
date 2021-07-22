<!doctype html>
<html>
<title><?= $stuDetail["candidate_name"]." - DETAIL" ?></title>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= base_url() ?>css/mystyle.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<style>
td{
padding:15px;
font-size:15px;
font-weight:500;
color:#000000;
width:170px;
}
td:nth-child(1)
{
	width:200px;

}
td:nth-child(2)
{
	width:2px;
	
}
td:nth-child(3){
	width:250px;
	
}
td:nth-child(4){
	width:100px;
}
td:nth-child(5){
	width:2px;
}
td:nth-child(6){
	width:200px;
}
</style>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-106135862-7"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-106135862-7');
</script>

</head>
<body>
<div class="container" style="width:700px; border:2px solid #ddd; height:auto; margin-top:10px; ">
<div style="padding:20px; text-align:center;">
	(<?= $stuDetail['institute_code'] ?>) <?= $stuDetail['institute_name'] ?>
</div>
<table>
	<tr>
		<td >Reg. No</td><td>: </td><td><?= $stuDetail["reg_no"] ?></td><td rowspan="5" colspan="3">
		<div style="width:100%; height:180px; padding-left:20px;">
		<img src="https://updeledexam.in/StudentsPhoto/<?= $stuDetail["candidate_photo"] ?>" alt="student Image" width="150px" height="180px" style="border:2px solid black;"/>
		</div></td>
		
	</tr>
	<tr>
		<td>Roll No </td><td>:</td><td> <?= $stuDetail["roll_no"] ?></td>
	</tr>
	<tr>
		<td>Candidate Name </td><td>:</td><td> <?= $stuDetail["candidate_name"] ?></td>
	</tr>
	<tr>
		<td>Father's Name </td><td>:</td><td> <?= $stuDetail["candidate_father_name"] ?></td>
	</tr>
	
	<tr>
		<td>Mother's Name</td><td> :</td><td> <?= $stuDetail["candidate_mother_name"] ?></td>
	</tr>
	<tr>
		<td>DOB (yyyy-mm-dd)</td><td> :</td><td><?= $stuDetail["candidate_dob"] ?></td>
	</tr>
	<tr>
		<td>Gender</td><td> :</td><td><?php if($stuDetail["candidate_gender"] == 1) echo "FEMALE"; elseif ($stuDetail["candidate_gender"] == 2) echo "MALE"; elseif($studtl["candidate_gender"] == 3) echo "TRANSGENDER"; else echo "";?></td>
		<td>Caste </td><td>:</td><td><?= $stuDetail["candidate_caste"] ?></td>
	</tr>
	<tr>
		<td>Email Id </td><td>:</td><td colspan="4"><?= $stuDetail["candidate_email"] ?></td>
	</tr>
	<tr>
		<td>Mobile Number </td><td>:</td><td> <?= $stuDetail["candidate_mob_no"] ?></td>
	</tr>
	<tr>
		<td>Id Type </td><td>:</td><td><?= $stuDetail["candidate_id_type"] ?></td><td>Id No. </td><td>:</td><td> <?= $stuDetail["candidate_id_no"] ?></td>
	</tr>
	<tr>
	    <td>Status</td><td>:</td><td><?php if($stuDetail['edit_status'] == 1){ echo "Edited (Last Updated on : ".$stuDetail['updt_time'].")";  } else { echo "Not Edited"; }?></td>
	 <tr>
	
	
	
</table>
<div style="margin-top:50px;">
<h3 class="text-center"><u>Instruction</u></h3>
<ol>
<li>अपने Records में किसी भी त्रुटि के सुधार के लिए अपने कॉलेज से संपर्क करें ।</li>
<li>Records में सुधार का अधिकार केवल डायट को दिया गया है डायट द्वारा Record में सुधार करने के उपरांत आपको Record चेक करने पर Status में Not Edited की जगह Edited दिखने लगेगा । इसलिए आवेदन करने बाद समय-समय पर अपना Record चेक करते रहें ।</li>
<li>Status में Edited होने के बाद भी यदि आपके रिकॉर्ड में कोई त्रुटि रह जाती है तो इसके लिए भी अपने कॉलेज से संपर्क करें और उस गलती का सुधार करा लें । </li>
<li>आपके Records की त्रुटियों में सुधार कराने का यह प्रथम और अंतिम अवसर है इसके बाद आपके Records में किसी भी प्रकार के Changes नहीं किए जा सकेंगे ।</li>
</ol>
</div>
</div>

</body>
<script>
window.print();
</script>
</html>