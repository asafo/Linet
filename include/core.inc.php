<?
$articlestbl = "articles";
$picstbl = "pics";
$companiestbl = "companies";
$histtbl = "contacthist";
$logintbl = "login";
$permissionstbl = "premissions";
$currencytbl = "currency";
$ratestbl = "rates";
$accountstbl = "accounts";
$contactstbl = "contacts";
$docstbl = "docs";
$docdetailstbl = "docdetails";
$catalogtbl = "catalog";
$transactionstbl = "transactions";
$supdocstbl = "supdocs";
$supdocdetailstbl = "supdocdetails";
$receiptstbl = "receipts";
$bankbooktbl = "bankbook";
$chequestbl = "cheques";
$tranpattbl = "tranpatt";
$tranreptbl = "tranrep";
$itemstbl = "items";
$stattbl = "stat";

$softwarename = "Linet";		 /* up to 20 characters */
$softwarenameheb = _("Linet - Free accounting software based on Drorit");
$softwaremaker = "Speedcomp"; /* up to 20 characters */
$softwaremakerregnum = "069924504";
$softregnum = "00179401";	/* Software registration number in tax authorities */

$title = $softwarenameheb;

// Account types 
$AcctType[0] = _("Customers");
// $AcctType[0] = '������';
$AcctType[1] = _("Suppliers");
// $AcctType[1] = '�����';
$AcctType[2] = _("Outcomes");
// $AcctType[2] = '������ ����� �������';
$AcctType[3] = _("Incomes");
// $AcctType[3] = '������';
$AcctType[4] = _("Authorities");
// $AcctType[4] = '������';
$AcctType[5] = _("Liabilities");
// $AcctType[5] = '���������';
$AcctType[6] = _("Equity");
// $AcctType[6] = '��� ����';
$AcctType[7] = _("Buys");
// $AcctType[7] = '�����';
$AcctType[8] = _("Banks");
// $AcctType[8] = '�����';
$AcctType[9] = _("Cash");
// $AcctType[9] = '�������';
$AcctType[10] = _("financing expenses");
// $AcctType[10] = '������ �����';
$AcctType[11] = _("Stocks");
// $AcctType[11] = '��"�';
$AcctType[12] = _("Assets");
// $AcctType[12] = '���� ����';

define("CUSTOMER", 0);
define("SUPPLIER", 1);
define("OUTCOME", 2);
define("INCOME", 3);
define("AUTHORITIES", 4);
define("OBLIGATIONS", 5);
define("CAPITAL", 6);
define("BUYS", 7);
define("BANKS", 8);
define("CASH", 9);
define("FINANCING", 10);
define("STOCKS", 11);
define("ASSETS", 12);
define("CONTACT", 20);

// Predefined accounts 
define("BUYVAT", 1);	// ��"� ������
define("ASSETVAT", 2);	// ��"� ������ ���� ������
define("SELLVAT", 3);	// ��"� ������
define("PAYVAT", 4);	// ��"� ��"�
define("OUTCOMECLEAR", 5);	// ����� ����� ������
define("ROUNDING", 6);		// ����� ������
define("CHEQUE", 7);	// ���� �����
define("CUSTTAX", 8);	// ����� ����� �������
define("OPENBALANCE", 9);	// ����� �����
define("ACCTCASH", 10);	// ���� �������
define("CREDIT", 11);	// ���� �����
define("DEPOSITS", 12);	// �������
define("PRETAX", 13);	// �� ����� ������
define("NATINSPAY", 14);	// ����� ����� ��"�
define("NATINS", 15);	// ����� �����
define("IRS", 16);	// �� �����
define("EQOUTCOME", 17);	// ���� �����
define("SALARY", 18);	// �������
define("PRETAX", 105); // ������ �� �����
define("OPEN_STOCK", 106);	// ���� �����
define("BUY_STOCK", 107);	// �����
define("CLOSE_STOCK", 108);	// ���� �����
define("GENOUTCOME", 109); // ������ �����
define("EMPLOYEESALARY", 110);	// ������ ��"�
define("CARGAS", 111);	// ���
define("COMMUNICATION", 112);	// ������ 
define("GENCUSTOMER", 113);	// ������ �����
define("GENSUPPLIER", 114);	// ����� �����

$DocType[1] = _("Proforma");
// $DocType[1] = '����� ����';
$DocType[2] = _("Delivery doc.");
// $DocType[2] = '�. �����';
$DocType[3] = _("Invoice");
// $DocType[3] = '������� ��';
$DocType[4] = _("Credit invoice");
// $DocType[4] = '������� �����';
$DocType[5] = _("Return document");
// $DocType[5] = '����� �����';
$DocType[6] = _("Receipt");
// $DocType[6] = '����';
$DocType[7] = _("Quote");//adam:
$DocType[8] = _("Sales Order");

define("DOC_PROFORMA", 1);
define("DOC_DELIVERY", 2);
define("DOC_INVOICE", 3);
define("DOC_CREDIT", 4);
define("DOC_RETURN", 5);
define("DOC_RECEIPT", 6);

// Document status definitions
define("OPEN", 0);
define("CLOSED", 1);

// Predefined types of transactions
define("MANUAL", 0);
define("INVOICE", 1);	// �������
define("SUPINV", 2);	// ������� ���
define("RECEIPT", 3);	// ����
define("CHEQUEDEPOSIT", 4);
define("SUPPLIERPAYMENT", 5);
define("VAT", 6);	// ��"�
define("STORENO", 7);
define("BANKMATCH", 8);
define("SRCTAX", 9);
define("PATTERN", 10);
define("MANINVOICE", 11);
define("MANRECEIPT", 13);
define("TRAN_PRETAX", 14);
define("TRAN_SALARY", 15);
define("OPBALANCE", 16);

$TranType[0] = _("Manual");
// $TranType[0] = '���� ����';
$TranType[1] = _("Invoice");
// $TranType[1] = '�������';
$TranType[2] = _("Supplier invoice");
// $TranType[2] = '������� ���';
$TranType[3] = _("Receipt");
// $TranType[3] = '����';
$TranType[4] = _("Cheque deposit");
// $TranType[4] = '����� ���';
$TranType[5] = _("Supplier payment");
// $TranType[5] = '����� ����';
$TranType[6] = _("VAT");
// $TranType[6] = '��\"�';
$TranType[7] = _("Storeno");
// $TranType[7] = '������';
$TranType[8] = _("Bank reconciliation");
// $TranType[8] = '����� ���';
$TranType[9] = _("Source tax");
// $TranType[9] = '����� �����';
$TranType[10] = _("Transaction pattern");
// $TranType[10] = '����� �����';
$TranType[11] = _("Manual invoice");
// $TranType[11] = '������� �����';
$TranType[13] = _("Manual receipt");
// $TranType[13] = '���� �����';
$TranType[14] = _("Tax prepayment");
// $TranType[14] = '�� ����� ������';
$TranType[15] = _("Salary");
// $TranType[15] = '������';
$TranType[16] = _("Openning balance");

// $UnitArr = array('��� ������', '���� �����', '������', '����', '���', '�������', '���');
$UnitArr = array(_("No units"), _("work hours"), _("units"), _("liter"), _("gram"), _("Kilo gram"), _("Meter"));

$paymenttype = array(
		//1 => _("Cash"), 
		1 => _("�����"), 
		//2 => _("Cheque"),
		2 => _("�'�"),
		//3 => _("Credit card"),
		3 => _("����� �����"),
		//4 => _("Bank transfer")
		4 => _("����� ������")
	);

$creditcompanies = array(
		//0 => _("No credit"),
		0 => _("��� �����"),
		//1 => _("Isracard"),
		1 => _("��������"),
		//2 => _("Cal"),
		2 => _("���"),
		//3 => _("Diners"),
		3 => _("������"),
		//4 => _("American express"),
		4 => _("������ ������"),
		//6 => _("Leumi card")
		6 => _("����� ����")
		);

$banksarr = array(10 => '�����',
	12 => '������',
	20 => '����� �����',
	11 => '��� ������� ������',
	01 => '��������',
	99 => '�����',
	14 => '���� �����',
/*
��� ���� ������-13
��� ������ ������-20
��� ������� �����-24
��� ��������� ������ ������-31
��� ���-04
��� �����-08
��� ������� �������-17
��� ���� ������-26
��� ��������� ������-28
��� ����� �������-47
���� ��� - 22 */
);

?>