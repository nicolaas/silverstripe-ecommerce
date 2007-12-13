<?php

// base class for a report item in left pane of CMS
class Report extends ViewableData {
	
	static $title = "";
	static $description = "";
	
		/**
	 * Returns a FieldSet with which to create the CMS editing form.
	 * You can use the extend() method of FieldSet to create customised forms for your other
	 * data objects.
	 */
		
	function getCMSFields($controller = null) {
		require_once("forms/Form.php");
		
		Requirements::css("ecommerce/css/DataReportCMSMain.css");
		Requirements::javascript("ecommerce/javascript/DataReport.js");
	
		$ret = new FieldSet(
			new TabSet("Root",
				new Tab("Report",
					new LiteralField('ReportTitle',"<h3>{$this->stat('title')}</h3>"),
					new LiteralField('ReportDescription',"<p>{$this->stat('description')}</p>"),
					$field = $this->getReportField()
				)
			)
		);		
		return $ret;
	}
	
	function TreeTitle() {
		$title = $this->stat('title');
		return $title ? $title : $this->class;
	}
	
	function ID() {
		return $this->getOwnerID();
	}
}


// Customized Report item
class Report_UnprintedOrder extends Report{

	static $title = "Unprinted Orders";
	static $description = "This shows all orders that are complete, but haven't been printed yet.";

	function getReportField() {

		$now = strftime("%d/%m/%Y", time());
		
		$orderReport = new OrderReport("OrderReport",null,"",null,"Order",
			$fieldmap = array(
				"Order Number"=>"Order.ID",
				"Order Date"=>"`Order`.Created->Date",
				"Customer Name" => "Member.Surname",
				"Order Total"=>"Total->Nice",
				"Status"=>"`Order`.Status",
				// The SS 2.0.2 reporting engine isn't good enough to do what we need to do here
				// "Method"=>"`Payment`.ClassName",
				"Last Status Change"=>"OrderStatusLog.Created->Date",
			),
			$headField = null,
			$filter = array(
				"Printed" => 0 ,
			),
			$dateFilter = array("From"=>"20/07/2006", "To"=>$now),
			$sort = array("Order.ID"=>"DESC"),
			$join = array(
				"LEFT JOIN Member ON Member.ID = `Order`.MemberID",				
				"LEFT JOIN OrderStatusLog ON OrderStatusLog.OrderID = `Order`.ID",				
				"LEFT JOIN Payment ON Payment.OrderID = `Order`.ID",				
			)
		);
		$orderReport->setCustomSelect(array(
			"Member.Surname",
			"OrderStatusLog.Created",
			"Payment.ClassName",
		));
		$orderReport->setExtraFields(array(
			"Print" => '<a href=\"OrderReport_Popup/index/$record->ID/?print=1\">print</a>',
			"Packing Slip" => '<a href=\"OrderReport_Popup/packingSlip/$record->ID\">view</a>'
		));

		return $orderReport;
	}
	
	//Customized ID this class.
	function getOwnerID(){
		return $this->class;
	}
}

// Customized Report item
class Report_CurrentOrders extends Report{

	static $title = "Current Orders";
	static $description = "This shows all orders that are not paid or cancelled.";

	//Customized Header and Content in reported table
	function getReportField() {

		$now = strftime("%d/%m/%Y", time());
		
		$orderReport = new OrderReport("OrderReport",null,"",null,"Order",
			$fieldmap = array(
				"Order Number"=>"Order.ID",
				"Order Date"=>"`Order`.Created->Date",
				"Customer Name" => "Member.Surname",
				"Order Total"=>"Total->Nice",
				"Status"=>"`Order`.Status",
				// The SS 2.0.2 reporting engine isn't good enough to do what we need to do here
				// "Method"=>"`Payment`.ClassName",
				"Last Status Change"=>"OrderStatusLog.Created->Date",
			),
			$headField = null,
			$filter = array(
				"Order.Status NOT IN ('Complete', 'Cancelled')",		
			),
			$dateFilter = array("From"=>"20/07/2006", "To"=>$now),
			$sort = array("Order.ID"=>"DESC"),
			$join = array(
				"LEFT JOIN Member ON Member.ID = `Order`.MemberID",				
				"LEFT JOIN OrderStatusLog ON OrderStatusLog.OrderID = `Order`.ID",				
				"LEFT JOIN Payment ON Payment.OrderID = `Order`.ID",				
			)
		);
		$orderReport->setCustomSelect(array(
			"Member.Surname",
			"OrderStatusLog.Created",
		));
		$orderReport->setExtraFields(array(
			"Print" => '<a href=\"OrderReport_Popup/index/$record->ID/?print=1\">print</a>',
		));

		return $orderReport;
	}
	
	//Customized ID this class.
	function getOwnerID(){
		return $this->class;
	}
}

// Customized Report item
class Report_FindAnOrder extends Report{

	static $title = "Find an Order";
	static $description = "You can find an order by defining the search parameters below.";

	//Customized Header and Content in reported table, customized filter on the report
	function getReportField() {
	
		$now = strftime("%d/%m/%Y", time());

		$orderReport = new FindOrderReport(
			"FindOrderReport",
			null,
			"",
			null,
			"Order",
			$fieldmap = array(
				"Order Number"=>"Order.ID",
				"Order Date"=>"`Order`.Created->Date",
				"Customer Name" => "Member.Surname",
				"Order Total"=>"Total->Nice",
				"Status"=>"`Order`.Status",
				// The SS 2.0.2 reporting engine isn't good enough to do what we need to do here
				// "Method"=>"`Payment`.ClassName",
				"Last Status Change"=>"OrderStatusLog.Created->Date",
			),
			$headField = null,
			$filter = array(
				"OrderType" => "All",
				"`Order`.ID" => "All",
				"MemberID" => "All",
			),
			$dateFilter = array(
				"From"=>"20/07/2006", 
				"To"=>$now
			),
			$sort = array(
				"`Order`.ID" => "DESC",
				"OrderStatusLog.Created" => "DESC"
			),
			$join = array(
				"LEFT JOIN Member ON Member.ID = `Order`.MemberID",				
				"LEFT JOIN OrderStatusLog ON OrderStatusLog.OrderID = `Order`.ID",				
				"LEFT JOIN Payment ON Payment.OrderID = `Order`.ID",				
			)
		);
		$orderReport->setCustomSelect(array(
			"Member.Surname",
			"OrderStatusLog.Created",
//			"Payment.PaymentMethod",
		));
		$orderReport->setExtraFields(array(
			"Print" => '<a href=\"OrderReport_Popup/index/$record->ID/?print=1\">print</a>',
			"Packing Slip" => '<a href=\"OrderReport_Popup/packingSlip/$record->ID\">view</a>'
		));
		return $orderReport;
	}
	
	// Customized Report item
	function getOwnerID(){
		return $this->class;
	}
}

/*class Report_StatsReport extends Report {
	
	static $title = "Website statistics";
	static $description = "View statistics of this website";
	
	static $awstatsURL;
		static function set_awstatsURL($pass) {
		self::$awstatsURL = $pass;
	}
	
	function getReportField() {
		
		$awstatsURL = self::$awstatsURL;
		
		return new LiteralField( 'AWStats', "<iframe name=\"reportframe\" class=\"AWStatsReport\" src=\"$awstatsURL\"></iframe>" );
	}
	
	function getOwnerID() {
		return $this->class;
	}	
}
*/
class Report_AllProducts extends Report {
	
	static $title = "View all Products";
	static $description = "This allows you to view a detailed report of all the products in the system, in bulk format.";
	
	function getReportField() {
		return new LiteralField('Info',
				"<a href=\"ViewAllProducts/\" class=\"popup viewAll\" target=\"_blank\">Click here to view all products</a>");
	}
	
	function getOwnerID() {
		return $this->class;
	}	
}

class Report_ProductPopularity extends Report {

	static $title = "Product popularity";
	static $description = "This shows the most popular products ordered by users.";
	
	function getReportField() {
		$report = new SQLReport(
			"Report_ProductReport",
			"Product popularity",
			null,
			null,
			array(
				'Product Code' => 'InternalItemID', 
				'Product name' => 'Name',
				'Total orders' => 'NumberOfOrders',
				'Current unit price' => 'CurrentUnitPrice',
				'Total order value' => 'TotalOrderValue',
				'Last ordered' => 'LastOrdered' 
			),
			null,
			"SELECT `SiteTree`.`Title` AS 'Name', " .
			"	`Product`.`InternalItemID` AS 'InternalItemID', " .
			"	`Order_Item`.`Quantity` AS 'NumberOfOrders', " .
			"	`Product`.`Price` AS 'CurrentUnitPrice', " .
			"	( `Order_Item`.`Quantity` * `Order_Item`.`UnitPrice` ) AS 'TotalOrderValue', " .
			"	MAX( `Order`.`Created` ) AS 'LastOrdered' " .
			"FROM `Product` " .
			"	LEFT JOIN `SiteTree` USING (`ID`) " .
			"	LEFT JOIN `Order_Item` ON `Order_Item`.`ProductID` =`Product`.`ID`" .
			"	LEFT JOIN `Order` ON `Order`.`ID`=`OrderID`" .
			"WHERE `Order_Item`.`Quantity` > 0 " .
			"GROUP BY `Product`.`InternalItemID`" .
			"ORDER BY TotalOrderValue DESC , `SiteTree`.`Title` ASC"
		);
		
		// TODO Fix up exportToCSV
		$report->export = false;
		
		return $report;
	}
	
	function getOwnerID() {
		return $this->class;
	}
}

/**
 * A controller class work for exporting reported table to CSV format.
 */
class Report_ProductReport_Controller extends Controller{
	static $title = "Product popularity"; 
	static $description = "Breakdown of numbers of units of items sold";     

	/**The function is an action for making $Controller/$action/$ID works when click on Exporting button.
		*Todo: Declear a new OrderReport Object with null filter, then set its filter using $_GET global, then call its export function.
		*/
	function exporttocsv() {
		// $id = $this->urlParams['ID'];
		
		$orderReport = new SQLReport(
			"Report_ProductReport",
			"Product popularity",
			null,
			null,
			array(
				'Product Code' => 'InternalItemID', 
				'Product name' => 'Name',
				'Total orders' => 'NumberOfOrders',
				'Current unit price' => 'CurrentUnitPrice',
				'Total order value' => 'TotalOrderValue',
				'Last ordered' => 'LastOrdered' 
			),
			null,
			"SELECT `SiteTree`.`Title` AS 'Name', " .
			"	`Product`.`InternalItemID` AS 'InternalItemID', " .
			"	COUNT(`Order_Item`.`ID`) AS 'NumberOfOrders', " .
			"	`Product`.`Price` AS 'CurrentUnitPrice', " .
			"	SUM( `Order_Item`.`Quantity` * `Order_Item`.`UnitPrice` ) AS 'TotalOrderValue', " .
			"	MAX( `Order`.`Created` ) AS 'LastOrdered' " .
			"FROM `Product` " .
			"	LEFT JOIN `SiteTree` USING (`ID`) " .
			"	LEFT JOIN `Order_Item` ON `Order_Item`.`ProductID`=`Product`.`ID`" .
			"	LEFT JOIN `Order` ON `Order`.`ID`=`OrderID`" .
			"GROUP BY `Product`.`InternalItemID`" .
			"ORDER BY TotalOrderValue DESC , `SiteTree`.`Title` ASC"
		);
		// $orderReport->filter_onchange();
		$orderReport->exportToCSV("report.csv");
	}
}
?>