DataReport = Class.create();
DataReport.prototype = {
	initialize: function() {

		Behaviour.register({
			'#ReportFilter input' : {
				onblur: this.filterOnChange.bind(this)
			},
			'#ReportFilter select' : {
				onchange: this.filterOnChange.bind(this)
			},
			'#Go' : {
				onclick: this.filterOnChange.bind(this)
			},
			'input.DataReport_ExportToCSVButton' : {
				onclick: function() {
					var pageID = $('Form_EditForm_ID').value;
					var idParts = this.id.split('_');
					var formName = idParts[idParts.length-2];
				
					var reportType = $('DataReport_Type').value;
					var url = baseHref() + 'admin/ReportField_Controller/exporttocsv/' + pageID + '/' + reportType + '/' + formName + '.csv';
					
					//alert( url );
					//var exportWindow = window.open( url, '_self' );
					//alert( exportWindow.location );
					window.location.href = url;
					
					return false;
				}
			}
		});
	},
	
	printAllNewWindow: function(e) {
		window.open($('printAll').href, "printWindow");
		Event.stop(e);
	},
	
	filterOnChange: function (e) {
		if(!e) e = window.event;
		var form = Event.findElement(e,'form');
		var url = form.action + "&action_callfieldmethod=1&fieldName=FindOrderReport&ajax=1&methodName=filter_onchange";
		new Ajax.Updater(
			{success: 'ReportList_Loader'},
			url,
			{
				method: 'post', 
				postBody: Form.serializeWithoutButtons(form), 
				onFailure: ajaxErrorHandler, 
				onComplete : statusMessage('Loaded report')
			}
		);
		Event.stop(e);
	}
}
DataReport.applyTo('#FindOrderReport');