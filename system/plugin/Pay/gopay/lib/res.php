
<? 

$version = $_POST["version"];
$charset = $_POST["charset"];
$language = $_POST["language"];
$signType = $_POST["signType"];
$tranCode = $_POST["tranCode"];
$merchantID = $_POST["merchantID"];
$merOrderNum = $_POST["merOrderNum"];
$tranAmt = $_POST["tranAmt"];
$feeAmt = $_POST["feeAmt"];
$frontMerUrl = $_POST["frontMerUrl"];
$backgroundMerUrl = $_POST["backgroundMerUrl"];
$tranDateTime = $_POST["tranDateTime"];
$tranIP = $_POST["tranIP"];
$respCode = $_POST["respCode"];
$msgExt = $_POST["msgExt"];
$orderId = $_POST["orderId"];
$gopayOutOrderId = $_POST["gopayOutOrderId"];
$bankCode = $_POST["bankCode"];
$tranFinishTime = $_POST["tranFinishTime"];
$merRemark1 = $_POST["merRemark1"];
$merRemark2 = $_POST["merRemark2"];
$signValue = $_POST["signValue"];
     
      


$signValue = $_POST["signValue"];

//ע��md5���ܴ���Ҫ����ƴװ���ܺ����ȡ�������Ĵ�������ǩ
$signValue2='version=['.$version.']tranCode=['.$tranCode.']merchantID=['.$merchantID.']merOrderNum=['.$merOrderNum.']tranAmt=['.$tranAmt.']feeAmt=['.$feeAmt.']tranDateTime=['.$tranDateTime.']frontMerUrl=['.$frontMerUrl.']backgroundMerUrl=['.$backgroundMerUrl.']orderId=['.$orderId.']gopayOutOrderId=['.$gopayOutOrderId.']tranIP=['.$tranIP.']respCode=['.$respCode.']gopayServerTime=[]VerficationCode=[12345678]';
//VerficationCode���̻�ʶ����Ϊ�û���Ҫ��Ϣ�����Ʊ���
//ע�������������ʱ��Ҫ�޸����ֵΪ��������



$signValue2 = md5($signValue2);

if($signValue==$signValue2){
	if($respCode=='0000')
	
	  //��ǩ�ɹ�
	  //�����ڴ˴������̻���ҵ���߼�����
	  //ע�ⷵ�ز����в������û���session��cookie
	  echo 'RespCode=0000|JumpURL=http://127.0.0.1:8080/true.php?aa=5'; 
	  //���Ҫ������תָ����ַ������Ӧ�������Ϲ淶���ο��ĵ���5.	֪ͨ����
	  
	else
	
	  //��ǩʧ��
    //����ʧ����Ϣ
	  echo 'RespCode=9999|JumpURL='; 
	  
}


?>
		
		
		
	







