<?php
$module=$_GET['dest'];
$url='http://www.linet.org.il/index.php/support/';

$bla=array(
'defs'=>'user-help-navigate/8-configuration-help/18-biz-details-page',//����� ���� ���
'acctadmin'=>'user-help-navigate?id=19',//�������
'acctadmin0'=>'user-help-navigate?id=48',
'acctadmin1'=>'user-help-navigate?id=45',
'acctadmin2'=>'user-help-navigate?id=44',
'acctadmin3'=>'user-help-navigate?id=47',
'acctadmin6'=>'user-help-navigate?id=52',///������
'acctadmin5'=>'user-help-navigate?id=53',//����������
'acctadmin4'=>'user-help-navigate?id=56',///�����
'acctadmin7'=>'user-help-navigate?id=51',//�����
//'acctadmin9'=>'user-help-navigate?id=57',//�������
//'acctadmin11'=>'user-help-navigate?id=55',//����� ���
//'acctadmin12'=>'user-help-navigate?id=56',//���� ����
'docnums'=>'user-help-navigate?id=23',//������ ������� ������
'items'=>'user-help-navigate?id=28',//�����
'curadmin'=>'user-help-navigate?id=29',//���� ����
'opbalance'=>'user-help-navigate?id=30',//����� �����
'loginedituser'=>'user-help-navigate?id=31',//����� ���� �����
'loginadduser'=>'user-help-navigate?id=32',//���� �����
'contact0'=>'user-help-navigate?id=24',//����� ������
'contactedit'=>'user-help-navigate?id=27',//����
'docsadmin6'=>'user-help-navigate?id=33',//������: ����� ����
'docsadmin7'=>'user-help-navigate?id=34',//����� �����
'docsadmin1'=>'user-help-navigate?id=35',//������� ����
'docsadmin3'=>'user-help-navigate?id=36',//������� ��
'docsadmin9'=>'user-help-navigate?id=38',//������� �� ����
'docsadmin4'=>'user-help-navigate?id=40',//������� �����
'deposit'=>'user-help-navigate?id=41',//������ ���
'contact1'=>'user-help-navigate?id=25',//����� �����
'outcome'=>'user-help-navigate?id=43',//����� �����
'outcomeasset'=>'user-help-navigate?id=46',//����� ���
'showdocs'=>'user-help-navigate?id=42',



'payment'=>'user-help-navigate?id=60',//����� ����
'paymentvat'=>'user-help-navigate?id=61',//���
'paymentnatins'=>'user-help-navigate?id=62',//����� �����
'docsadmin8'=>'user-help-navigate?id=39',//����
//�����
'tranrep'=>'user-help-navigate?id=68',//��� ������ ������
'owe'=>'user-help-navigate?id=71',//������
'profloss'=>'user-help-navigate?id=73',//��� ���� �����
'mprofloss'=>'user-help-navigate?id=74',//��� ���� ����� ��� ����
'vatrep'=>'user-help-navigate?id=75',//��� ��"�

//������
'bankbook'=>'user-help-navigate?id=59',//����� ��� ���
'extmatch'=>'user-help-navigate?id=65',//������ �����
'dispmatch'=>'user-help-navigate?id=66',//��� ������
'intmatch'=>'user-help-navigate?id=67',//������ �������


//���� ����
'openfrmt'=>'user-help-navigate?id=77',//���� ���� ����
'pcn874'=>'user-help-navigate?id=80',//��� PCN874
'backup'=>'user-help-navigate?id=78',//����� ����
'backuprestore'=>'user-help-navigate?id=79',//����� ����� ����
'openfrmtimport'=>'user-help-navigate?id=58',//���� ���� ����

//�����

//����� ������
//module=support,paid-support
//�����
//module=about,user-help-navigate?id=50






);

$url=$url.$bla[$module];

print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=$url\">\n";
?>