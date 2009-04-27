<?php
/**
 * Account page shows order history and a form to allow
 * the member to edit his/her details.
 * 
 * @package ecommerce
 */
class AccountPage extends Page {
	
	static $add_action = 'an Account Page';
	
	/**
	 * Returns the link or the URLSegment to the account page on this site
	 * @param boolean $urlSegment Return the URLSegment only
	 */
	static function find_link($urlSegment = false) {
		if(!$page = DataObject::get_one('AccountPage')) {
			user_error('No AccountPage was found. Please create one in the CMS!', E_USER_ERROR);
		}
		
		return ($urlSegment) ? $page->URLSegment : $page->Link();
	}
	
	/**
	 * Return a link to view the order on the account page.
	 *
	 * @param int|string $orderID ID of the order
	 * @param boolean $urlSegment Return the URLSegment only
	 */
	static function get_order_link($orderID, $urlSegment = false) {
		if(!$page = DataObject::get_one('AccountPage')) {
			user_error('No AccountPage was found. Please create one in the CMS!', E_USER_ERROR);
		}
		
		return ($urlSegment ? $page->URLSegment . '/' : $page->Link()) . 'order/' . $orderID;
	}
	
	/**
	 * Automatically create an AccountPage if one is not found
	 * on the site at the time the database is built (dev/build).
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		if(!DataObject::get_one('AccountPage')) {
			$page = new AccountPage();
			$page->Title = 'Account';
			$page->Content = '<p>This is the account page. It is used for shop users to login and change their member details if they have an account.</p>';
			$page->URLSegment = 'account';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			
			Database::alteration_message('Account page \'Account\' created', 'created');
		}
	}
}

class AccountPage_Controller extends Page_Controller {

	function init() {
		parent::init();
		
		Requirements::themedCSS('AccountPage');
		
		if(!Member::currentUserID()) {
			$messages = array(
				'default' => '<p class="message good">' . _t('AccountPage.Message', 'You\'ll need to login before you can access the account page. If you are not registered, you won\'t be able to access it until you make your first order, otherwise please enter your details below.') . '</p>',
				'logInAgain' => 'You have been logged out. If you would like to log in again, please do so below.'
			);
			
			Security::permissionFailure($this, $messages);
			return false;
		}
	}

	/**
	 * Return a form allowing the user to edit
	 * their details with the shop.
	 *
	 * @return ShopAccountForm
	 */
	function MemberForm() {
		return new ShopAccountForm($this, 'MemberForm');
	}
	
	/**
	 * Returns all {@link Order} records for this
	 * member that are completed.
	 *
	 * @return DataObjectSet
	 */
	function CompleteOrders() {
		$memberID = Member::currentUserID();
		$statusFilter = "Status IN ('" . implode("','", Order::$paid_status) . "')";
		return DataObject::get('Order', "MemberID = '$memberID' AND $statusFilter", "Created DESC");
	}
	
	/**
	 * Returns all {@link Order} records for this
	 * member that are incomplete.
	 *
	 * @return DataObjectSet
	 */
	function IncompleteOrders() {
		$memberID = Member::currentUserID();
		$statusFilter = "Status NOT IN ('" . implode("','", Order::$paid_status) . "')";
		return DataObject::get('Order', "MemberID = '$memberID' AND $statusFilter", "Created DESC");
	}
	
	/**
	 * Returns the order details of the order which id is in the url
	 * Precondition : a user is logged 
	 */
	function order() {
		Requirements::themedCSS('Order');
		Requirements::themedCSS('Order_print', 'print');
		
		$memberID = Member::currentUserID();
		$accountPageLink = self::find_link();
		
		if($orderID = Director::urlParam('ID')) {
			if($order = DataObject::get_one('Order', "ID = '$orderID' AND MemberID = '$memberID'")) {
				return array('Order' => $order);
			} else {
				return array(
					'Order' => false,
					'Message' => 'You do not have any order corresponding to this ID. However, you can <a href="' . $accountPageLink . '">edit your own personal details and view your orders.</a>.'
				);
			}
		} else {
			return array(
				'Order' => false,
				'Message' => 'There is no order by that ID. You can <a href="' . $accountPageLink . '">edit your own personal details and view your orders.</a>.'
			);
		}
	}
	
	/**
	 * Check if the user can cancel their order.
	 * @return boolean
	 */
	function CanCancel() {
		if($order = DataObject::get_by_id('Order', Director::urlParam('ID'))) {
			return $order->CanCancel();
		}
		return false;
	}
	
	/**
	 * Returns the form to cancel the current order,
	 * checking to see if they can cancel their order
	 * first of all.
	 *
	 * @return Order_CancelForm
	 */
	function CancelForm() {
		if($this->CanCancel()) {
			$orderID = (int) Director::urlParam('ID');
			return new Order_CancelForm($this, 'CancelForm', $orderID);
		}
	}
	
}
?>