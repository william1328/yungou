<?php


// cvn2���� 1������ 0:������
const SDK_CVN2_ENC = 0;
// ��Ч�ڼ��� 1:���� 0:������
const SDK_DATE_ENC = 0;
// ���ż��� 1������ 0:������
const SDK_PAN_ENC = 0;
 
// ######(��������ΪPM�������������Ի����ã������������ü��ĵ�˵��)#######
// ǩ��֤��·��
const SDK_SIGN_CERT_PATH = 'D:\wamp\www\YunGouCmsm\system\modules\pay\lib\unionpay\unionpay.pfx';

// ǩ��֤������
 const SDK_SIGN_CERT_PWD = '123456';
 
// ��ǩ֤��
const SDK_VERIFY_CERT_PATH = '';

// �������֤��
const SDK_ENCRYPT_CERT_PATH = '';

// ��ǩ֤��·��
const SDK_VERIFY_CERT_DIR = '';

// ǰ̨�����ַ
const SDK_FRONT_TRANS_URL = '';

// ��̨�����ַ
const SDK_BACK_TRANS_URL = '';

// ��������
const SDK_BATCH_TRANS_URL = '';

//���ʲ�ѯ�����ַ
const SDK_SINGLE_QUERY_URL = '';

//�ļ����������ַ
const SDK_FILE_QUERY_URL = '';

//�п����׵�ַ
const SDK_Card_Request_Url = '';

//App���׵�ַ
const SDK_App_Request_Url = '';


// ǰ̨֪ͨ��ַ (�̻���������֪ͨ��ַ)
const SDK_FRONT_NOTIFY_URL = 'http://ceshi.yyyg.com/YunGouCmsm/?/pay/unionpay_url/qiantai';
// ��̨֪ͨ��ַ (�̻���������֪ͨ��ַ)
const SDK_BACK_NOTIFY_URL = 'http://ceshi.yyyg.com/YunGouCmsm/?/pay/unionpay_url/houtai';


//�ļ�����Ŀ¼ 
const SDK_FILE_DOWN_PATH = 'd:\wamp\www\YunGouCmsm\system\modules\pay\lib\unionpay\file/';

//��־ Ŀ¼ 
const SDK_LOG_FILE_PATH = 'd:\wamp\www\YunGouCmsm\system\modules\pay\lib\unionpay\logs/';


//��־����
const SDK_LOG_LEVEL = 'INFO';


	
?>