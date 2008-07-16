<?php

/**
 * Displays complex reports based on base Table of DataObject and available functions/fields provided to
 * the object.
 */
class FindOrderReport extends OrderReport {
	
		/**
		*Todo: overwrite its parents FielderHolder. Mainly add filter here
		*/
	function FieldHolder() {
		
		$reportTableHTML = $this->htmlReportList();
		$reportFilter = $this->htmlfilter();
		$exportButton = $this->htmlExportButton();
		$printButton = $this->htmlPrintButton();
		return <<<HTML
<div id="FindOrderReport">
$reportFilter
$reportTableHTML
$exportButton
$printButton
</div>
HTML
;
	}
	
	/**
		*Todo: return HTML of the field filter and the dateFilter altogether
		*/
	protected function htmlfilter(){
		
		$dateFilter = $this->htmlDateRangeFilter();
		$fieldFilter = $this->htmlFieldFilter();
		$filterGo = $this->htmlfilterGo();

		return <<<HTML
<div id="ReportFilter">
$dateFilter
$fieldFilter
</div>
$filterGo
HTML
;
	}
	
		/**
		*Todo: return HTML of the dateFilter
		*/
	protected function htmlDateRangeFilter(){

		$dateFilter = new FieldGroup(_t("FindOrderReport.DATERANGE","Date Range"),
			new CalendarDateField("From", "From", $this->dateFilter['From'],null),
			new CalendarDateField("To", "To", $this->dateFilter['To'],null)
		);
		return $dateFilter->FieldHolder();
	}
	
		/**
		*Todo: return HTML of the field filter
		*/
	protected function htmlFieldFilter(){
		$order = singleton('Order');
		
//		$orderType = array(
//			"All"=>"All",
//			"Normal"=>"Normal",
//		);

		if(isset($this->filter['MemberName']) && $this->filter['MemberName'] != 'All'){
			$member = DataObject::get_by_id("Member", $this->filter['MemberName']);
			$name = $member->FirstName." ".$member->Surname;
		}else{
			$name = "All";
		}
		$fieldFilter = new CompositeField(
//			$orderType = 	new DropdownField("OrderType", "Type", $orderType, $this->filter[OrderType], null),
			$memberID = 	new TextField("MemberName", "Name", $name, null),
			$id =	new TextField("OrderID", "Order #", $this->filter['`Order`.ID'], null),
			$order->dbObject('Status')->formField("Status", null, true, "Unpaid")
		);

		return $fieldFilter->FieldHolder();
	}
	
	//return a dummy button Go in HTML format
	protected function htmlfilterGo(){
		return <<<HTML
<input style="width: 12em" class="action" id="Go" name="Go" value="Search" type="button">
HTML
;
	}
}

?>
