<?php

class UnprintedOrderReport extends SSReport {

	protected $title = 'Unprinted Orders';

	protected $description = 'This shows all orders that are complete, but haven\'t been printed yet.';

	/**
	 * @TODO Replace this with something like a TableListField.
	 */
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
			"Invoice" => '<a href=\"OrderReport_Popup/invoice/$record->ID/\">'._t("SSReport.INVOICE", 'Invoice').'</a>',
			"Print" => '<a href=\"OrderReport_Popup/index/$record->ID/?print=1\">'._t("SSReport.PRINT", 'Print').'</a>',
			"Packing Slip" => '<a href=\"OrderReport_Popup/packingSlip/$record->ID\">'._t("SSReport.VIEW", 'View').'</a>'
			));

			return $orderReport;
	}

}

?>