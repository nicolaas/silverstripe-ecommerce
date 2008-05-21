<?php

/**
 * Displays complex reports based on base Table of DataObject and available functions/fields provided to
 * the object.
 */
class OrderReport extends DataReport {
	
	function FieldHolder(){
		$reportList = $this->htmlReportList();
		$exportButton = $this->htmlExportButton();
		$printButton = $this->htmlPrintButton();
		return <<<HTML
		<div id="OrderReport">$reportList $exportButton $printButton</div>
HTML
;
	}
	
	
	function htmlPrintButton(){
		$idprint = $this->ID."_printEachOrder";
		$printeachorder = _t("OrderReport.PRINTEACHORDER","Print all orders shown");
		return $printButton = <<<HTML
<a href="OrderReport_Popup/index/All" class="popup">$record->ID<input name="$idprint" style="width: 12em" type="button" id="$idprint" class="DataReport_PrintEachOrderButton" value="$printeachorder" /></a>
HTML
;
	}
	

	function filter_onchange(){
	
		if($_REQUEST['From']) $this->dateFilter['From'] = $_REQUEST['From'];
		if($_REQUEST['To']) $this->dateFilter['To'] = $_REQUEST['To'];
		if($_REQUEST['orderType']) $this->filter['OrderType'] = $_REQUEST['orderType'];
		
		// Dealing with the name field of the filter, let it search on Members FirstName or Surname or both and using "Like" so it can be a free text search
		if($_REQUEST['MemberName'] && $_REQUEST['MemberName'] !== 'All' && $_REQUEST['MemberName'] !== 'all') {
			list($firstname, $surname) = explode(" ", $_REQUEST['MemberName']);
			$SQL_firstname = Convert::raw2sql($firstname);
			$SQL_surname = Convert::raw2sql($surname);
			if($SQL_firstname || $surname) {
				if($SQL_firstname && $SQL_surname)
					$members = DataObject::get("Member", "`FirstName` LIKE '%{$SQL_firstname}%' OR `Surname` LIKE '%{$SQL_surname}%'");
				else if($SQL_firstname){
					$members = DataObject::get("Member", "`FirstName` LIKE '%{$SQL_firstname}%' OR `Surname` LIKE '%{$SQL_firstname}%'");
				}
				else if($SQL_surname)
					$members = DataObject::get("Member", "`FirstName` LIKE '%{$SQL_surname}%' OR `Surname` LIKE '%{$SQL_surname}%'");
				}
			if($members) {
				foreach($members as $member) {
					$memberIDs[] = $member->ID;
				}
				$this->filter['`Order`.MemberID'] = $memberIDs;
			} else {
				$this->filter['`Order`.MemberID'] = 0;
			}
		}
		// HACK Hacked together like the rest of this function...
		$this->filter['`Order`.Status'] = (!empty($_REQUEST['Status'])) ? Convert::raw2sql($_REQUEST['Status']) : "All";

		// Dealing with the Order Number field of the filter. In case it is a 
		if($_REQUEST['OrderID'] && $_REQUEST['OrderID'] !== 'All' && $_REQUEST['OrderID'] !== 'all'){
			$this->filter = null;
			$this->dateFilter = null;
			$this->filter['`Order`.ID'] = $_REQUEST['OrderID'];
		}
		
		if(Director::is_ajax()) {
			echo $this->htmlReportTable();
		}
	}
	
/**
 * Todo: overwrite parent's function. Mainly add <a> tag to orderID and MemberName, so that Order ID is linked
 * to a popup window for printing, and MemberName is linked to a popup for writing email.
 */
	function htmlTableDataCell($record, $field, $fieldIndex = null){
		
		$value = $this->getRecordFieldValue($record, $field);

		if($this->headFields[$fieldIndex] == "Method") {
			$value = Convert::raw2xml($value);
			$CSS_value = ereg_replace('(^-)|(-$)','',ereg_replace('[^A-Za-z0-9_-]+','-',$value));
			return "<td class=\"$CSS_value\">$value</td>";
		} else if($this->headFields[$fieldIndex] == "Customer Name") {
			$value = htmlentities($value);
			return "<td><a href=\"mailto:" . $record->MemberEmail(). "\">$value</a></td>";
		} else if($field == "Order.ID") {
			$url = "OrderReport_Popup/index/".$record->ID;
			return <<<HTML
<td>
	<a class="popup" href="$url">
	$value
	</a>
</td>
HTML
;
		} else {
			return <<<HTML
<td>
	$value
</td>
HTML
;
		}
	}
}
	
/**
 *  A controller class for dealing with popup windows with correct content
 */	
class OrderReport_Popup extends Controller {
	
	//Default action.
	function index() {
		return $this->renderWith('OrderInformation_Print');
	}
	
	function packingSlip() {
		return $this->renderWith('OrderInformation_PackingSlip');
	}
	
	function invoice(){
		return $this->renderWith('OrderInformation_Invoice');
	}
	
	function Link() {
		return "OrderReport_Popup/index/";
	}
	
	/**
	 * Function created for the purpose of checking for Cheque payment 
	 */
	function SingleOrder(){
		$id = $this->urlParams[ID];

		if(is_numeric($id)){
			$order = DataObject::get_by_id("Order", $id);
			$payment = $order->Payment();
			$cheque = false;
			if($payment->First()){
				$record = $payment->First();
			//	Debug::Show($record);
				if($record->ClassName == "ChequePayment"){
					$cheque = true;
				}
			}
			
			return new ArrayData(array(
					'DisplayFinalisedOrder' => $order,
					'IsCheque' => $cheque
				)
			);
		}
		
		return false;
	}
 	
	/**
		*Todo: get orders by ID or using the current filter if ID is not numeric such as 'all'.
		*/
	function DisplayFinalisedOrder(){
		$id = $this->urlParams[ID];

		if(is_numeric($id)){
			$order = DataObject::get_by_id("Order", $id);
			if(isset($_REQUEST['print'])) {
				$order->updatePrinted(true);
			}

			return $order;
		}else{
			
			$orderReport = new OrderReport("OrderReport",null,"",null,"Order");
			$orderReport -> filter_onchange();

			$orders = $orderReport -> getRecords();
			if(isset($_REQUEST['print'])) {
				foreach($orders as $order) {
					$order->updatePrinted(true);
				}
			}

			return $orders;
		}
	}
	
	function StatusForm() {
		Requirements::css('cms/css/layout.css');
		Requirements::css('cms/css/cms_right.css');
		Requirements::css('ecommerce/css/OrderReport.css');
		
		Requirements::javascript('jsparty/loader.js');
		Requirements::javascript('jsparty/behaviour.js');
		Requirements::javascript('jsparty/prototype.js');
		Requirements::javascript('jsparty/prototype_improvements.js');

		$id = (isset($_REQUEST['ID'])) ? $_REQUEST['ID'] : $this->urlParams[ID];

		if(is_numeric($id)) {
			$order = DataObject::get_by_id("Order", $id);
			$member = $order->Member();

			$fields = new FieldSet(
				new HeaderField(_t("OrderReport.CHANGESTATUS","Change Order Status"),3),
				$order->dbObject('Status')->formField("Status", null, null, $order->Status),
				new TextareaField('Note', _t("OrderReport.NOTEEMAIL","Note/Email")),
				new CheckboxField('SentToCustomer', sprintf(_t("OrderReport.SENDNOTETO", "Send this note to %s (%s)"), $member->Title, $member->Email) , true),
				new HiddenField('ID', 'ID', $order->ID)
			);
			$actions = new FieldSet(
				new FormAction("save", "Save")
			);
			
			$form = new Form($this, "StatusForm", $fields, $actions);
			return $form;
		}
	}
	
	function StatusLog() {
		$table = new TableListField(
			'StatusTable',
			'OrderStatusLog',
			array(
				"ID" => "ID",
				"Created" => "Created",
				"Status" => "Status",
				'Note' => 'Note',
				'SentToCustomer' => 'Sent to customer',
			),
			"OrderID = {$this->urlParams[ID]}"
		);
		$table->setFieldCasting(array(
			"Created" => "Date->Nice",
			"SentToCustomer" => "Boolean->Nice",
		));
		$table->IsReadOnly = true;
		
		return new Form(
			$this,
			'OrderStatusLogForm',
			new FieldSet(
				new HeaderField('Order Status History',3),
				new HiddenField('ID'),
				$table
			),
			new FieldSet()
		);
	}
	
	function save($data, $form) {
		if(!is_numeric($data['ID'])) {
			return false;
		}
		$order = DataObject::get_by_id("Order", $data['ID']);
		
		// if the status was changed or a note was added, create a new log-object
		if(!empty($data['Note']) || $data['Status'] != $order->Status) {
			$orderlog = new OrderStatusLog();
			$orderlog->OrderID = $order->ID;
			$form->saveInto($orderlog);
			$orderlog->write();
		}
		// save the order
		if($order) {
			$form->saveInto($order);
			$order->write();
		}

		// optionally send the note to the client
		if($_REQUEST['SentToCustomer']) {
			$order->sendStatusChange();
		}

		return FormResponse::respond();
	}

}

?>
