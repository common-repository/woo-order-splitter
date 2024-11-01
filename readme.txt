=== Order Splitter for WooCommerce ===
Contributors: fahadmahmood
Tags: split, clone, combine, split orders, split funds
Requires at least: 4.4
Tested up to: 6.6
Stable tag: 5.2.9
Requires PHP: 7.0
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
A great plugin to split WooCommerce orders. You can duplicate orders as well.

== Description ==
* Author: [Fahad Mahmood](https://www.androidbubbles.com/contact)
* Project URI: <http://androidbubble.com/blog/wordpress/plugins/woo-order-splitter>

WooCommerce is an awesome eCommerce plugin that allows you to sell anything and if you want to sell products that are not on stock yet, but you're sure that you'll have them soon in stock again? So Order Splitter for WooCommerce is a solution for you as you can create a rule for those items. All of the upcoming items can go in a separate orders section/status. It enables you to split, consolidate, clone, your crowd/combined/bulk orders using intelligent rules.

After activation there will be a Split icon in wp-admin > WooCommerce > orders list page within the order actions. Splits all order metadata and product data across into the new order ID. Order is created and a note is left in the new order of the older order ID for future reference. Order status is then set on hold awaiting admin to confirm payment. 

= Tags =
woocommerce, pending payments, failed, processing, completed, cancelled, refunded

= How to use this plugin? =
[youtube http://www.youtube.com/watch?v=wjClFEeYEzo]

 
== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. There will now be a Split icon in the to WooCommerce  order overview page within the order actions.


== Frequently Asked Questions ==

= General Queries =

**&#128073; 1. Does coupon work with child orders?**

Yes, coupons work with child orders. There are three options to manage coupons. Default, Equal and Ratio.
With default option selected, coupon will not be cloned or distributed among child orders.
Clone option allows you to apply the same coupon amount to child orders as parent order.
Ratio option will calculate child order totals and distribute discounted amount accordingly.
[youtube https://youtu.be/wF1FBPatBAU ]

Coupons without restrictions (e.g. order minimum 3 items or minimum amount etc.) will work absolutely fine. Order Splitter will split the coupon using Ratio option among all child orders. But coupons with restrictions will not be entertained. As if coupon restriction is "order minimum 3 items" and after splitting there is only one item or two items in child order, it will not be applicable for that child order.
To keep coupon history, you can turn off "Remove items from parent order after splitting".

**&#128073; 2. What is Single Order Case and how will rules work in this case?**

In case there is no split and parent order remains as it is. You can select a different status of the order. If you tick the checkbox for rules based status, rules will take priority and status will be changed according to the product based rule defined.

**&#128073; 3. What is an Empty Order Case?**

When "Remove items from parent order on split" is checked and after splitting parent order left with no items then order status will be changed according to this dropdown selection.

**&#128073; 4. How to add custom order status?**

In Order Status tab click "Add New". Define status name, select payment status of this order status and click "Add New". A success message will appear. Now you can proceed with custom order status for splitting.

**&#128073; 5. Is there a way to manually split an order?**

Please check the settings page right sidebar with optional checkbox items. Uncheck the first option, auto split. It will resolve the issue.
Then on orders list page, you will see an icon against each order row item, under actions column. If actions aren't visible, make it visible from screen options, right top of the page.

**&#128073; 6. I upgraded the plugin, now it is no longer a premium version. How do I fix this?**

You are requested to see the first email in which you received a method to update plugin automatically so it will remain premium version.

**&#128073; 7. Will it work with multi-currency plugin?**

It must deal with orders, regardless of currencies or exchange rates. It will simply split the items into multiple orders according to the split rules you will choose. So, answer is yes.

= Deposit & Partial Payments Based Splitting =

**&#128204; 1. Is it compatible with the "Advanced – Deposit & Partial Payments for WooCommerce"?**

Yes, it is compatible with the "Advanced – Deposit & Partial Payments for WooCommerce" and also "Advanced Partial/Deposit Payment For Woocommerce PRO".

**Follow these steps:**

**&#128206; 1. Select the split method "Group by Attributes Values"**

**&#128206; 2. Select sub-attributes under select "Available, In Stock, Special Offer"**

That's it.

**How does it work?**

It will simply group the due_payment related products in a separate order and other products separate. It also handles the partially paid order status for those orders which have "Due Payment" related products in them. Customer can pay for that splitted/child order later.


= Stock Based Splitting =

**&#128204; 1. How it splits an order multiple times upon stock update?**

[youtube http://www.youtube.com/watch?v=jHKa4NZ26Tc]


**&#128204; 2. How it works with 3PL (Warehouse Management System) upon stock update?**

[youtube http://www.youtube.com/watch?v=JWKgvaFU5p8]

Order Splitter can set different status for the orders of in-stock and out-stock items. For example, if in-stock order status is set to processing and out-stock orders status is set to pending payment.

There is a button for Backorder Automation that can change order status upon stock update.
	
**&#128204; 3. What is Backorder Automation?**

In short, on stock availability split the order again and set backorder status to in stock status accordingly.

Some warehouse management software (e.g. 3PL WHM ) process only orders with specific status like processing. When user use stock based splitting backorders can be set to different status from the in-stock order. When stock is updated user must change order status of backorders manually so that warehouse manager software can process the order. When Backorder Automation is turned on Order Splitter will change backorder status to the parent order status upon stock update. It will work fine even if you don't use any warehouse management software.

[youtube http://www.youtube.com/watch?v=AWBLwmF_Op0]

**&#128204; 4. YITH Pre-Order Compatibility**

[youtube http://www.youtube.com/watch?v=swHpd8-9H-s]


**&#128204; 5. We add meta values to the product through Stock Locations for WooComemrce from product page/cart page and I want to grouped items on basis of this meta. This is not an attribute or attribute value. Will items be grouped on basis of these meta values?**

Order Splitter can group items based on meta values those are not attributes or attributes values. There is a split method to Group by Order Item Meta Values to achieve these results.

[youtube http://www.youtube.com/watch?v=VyaF_20bg2U]

= Booking | Shipping | Rules | ACF =

**&#128206; 1. How it works with Booking & Appointment Plugin for WooCommerce?**

It works with an addon of Booking & Appointment Plugin for WooCommerce plugin. It can group items by date, so you can group items by day (same date), month and year. It can group items by payment type as well. For example, items with partial payments will be grouped in a parcel and other items will be grouped in another parcel.

Video Tutorial: [youtube https://youtu.be/wu0laPS8rOY]

**&#128206; 2. How does it work with shipping?**

[youtube https://youtu.be/5yKoAWYQMgY]
[youtube https://youtu.be/HiMXcSvc40I]

**&#128206; 3. How automatic settings work?**

[youtube http://www.youtube.com/watch?v=tOT4l7_GCIw]

**&#128206; 4. How order rules work?**

[youtube http://www.youtube.com/watch?v=nX9ir93V-ug]

**&#128206; 5. ACF | Advanced Custom fields**

[youtube http://www.youtube.com/watch?v=vQPe22hj8zU]


= How Taxes are being splitted? =

**&#128206; Tax Settings - I/O Method Example **

[youtube http://www.youtube.com/watch?v=C_EDYXy3ZMw]

= Subscription Split =

**&#128206; 1. Video Tutorial**

[youtube https://youtu.be/QHcih1FzPyQ]

**&#128206; 2. We deliver items multiple times with specific days interval in a subscription. Charges are deducted when first order is placed. Does this plugin split all items with single quantity in each order with selected delivery date? **

Yes, this plugin will split all items with single quantity in each order with selected delivery date. There are many options to set delivery interval types between first order and remaining deliveries. For example, if an item with 4 quantity is ordered and interval type “Order Delivery Date selected by Customer (Checkout Page)” is set for the first order and interval type “Progressive Order Delivery Date + Interval” is set for remaining deliveries. This order will be splitted into 4 orders with 1 item in each order. Interval between will be set as per settings.

**&#128206; 3. What will happen to subscription date related to the order? **
Subscription delivery date will be updated according to the splitted order as delivery date will come.

**&#128206; 4. When subscription will renew, will order be splitted again for next tier of deliveries automatically? **
On every renewal of subscription, the plugin will split the orders according to the criteria set on the settings page and update the subscription date too.


= Combination =

**&#128261; 1. How to Combine WooCommerce Orders?**

[youtube https://youtu.be/nOFOvDNtqdQ]

**&#128261; 2. How to Merge WooCommerce Orders?**

[youtube http://www.youtube.com/watch?v=qrZMZAuv-VU]

= Different Suppliers | Vendors =

**&#128279; 1. How does it work for split by Vendors?**

[youtube https://youtu.be/lMwE_2qkoFs]
[youtube https://youtu.be/hMQavLSYdvI]

**&#128279; 2. Products with various suppliers, does this plugin offer purchase request feature?**

This plugin can split orders to the different suppliers, but this will not send any purchase order request to suppliers. That part would require some actions.
For example: Person A orders 100 dozen banana and 10 crates of red apples, both items are from different suppliers like Supplier A and Supplier B.
So, this plugin will split this one order into two different orders like this:
Order#1
100 dozen banana
Order#2
10 crates of red apples

That's it.

These items are separated in your WooCommerce system, but nothing happens further. It will not send any purchase request to the supplier A and Supplier B.

**&#128279; 3. How can I achieve Vendor based split with Exclusive (Free) split method?**

Question: I want to ask, for example I order 2 products from vendor A and B, can I just make this order into 2 seperate order id without making the parent order? I already tried the exclusive but it didnt work, it always show the parent order.

Answer: Yes, it is possible by using vendor based split. Vendor based split is a PREMIUM feature.

To achieve the same results with exclusive method you have to select items differently but there should be only two types of products in your order always.

Vendor A and Vendor B

So exclusive will consider one of these as selected and others unselected. Like this you will be able to achieve the same results. But it will only work if only two vendors are involved. Multiple vendors products will not work with exclusive method. Group by Vendors is a recommended split method for this requirement.

**&#x1F517; 4. Can I hide parent order form my vendors after split?**

Question: How parent/original orders can be filtered from orders list and my account area after split?

Answer: Go to Order Statuses tab, on settings page, add new status. Select paid status "Orders are paid but hidden, if you want to keep but do not want to show."  Select newly created order status for the parent order on settings page. As a result parent order will not be visible to admin, customers and vendors as well in orders list.

= Quantity Split =

**&#128280; 1. How default option in quantity split works?**

Default option is compatible with WooCommerce PPOM (Personalized Product Option Manager) by N-Media. This plugin supports its Custom product fields so these will not get lost on order split and all custom product fields will get transferred into new splitted order. It can split variation of one product as well.

**&#128280; 2. How does quantity split work?**

1) Default:

This method will simply split all quantities into x1 in separate orders.

2) Custom & Eric Logic:

These methods will take the proportional value from item meta key "split".
e.g. 

A) 3:4 means keep 3 items in parent order and split 4 items in new order when 7 qty. was ordered
B) 1:1 means keep 1 item in parent order and split 1 item in new order when 2 qty. was ordered
C) 2 means keep remaining items in parent order and split 2 items in new order

Note: Difference between custom and Eric Logic is, selection of the items in order. You can make selection while splitting, so you can exclude a few from split.

[youtube https://youtu.be/KSl_5VC1PPs]

How Eric Logic works?

i) Turn off auto split and original order removal first from settings page.
ii) From order list edit order you want to split with Eric Logic. 
iii) On order edit page, hover on items under item box. There will appear a pencil icon after total amount of item. Click the pencil icon to edit item then click "Add meta" and two fields will appear. 

iv) Fill the first field with "split" and the second with ratio as you want to split items and save it.

e.g. 4:4 for parent:child order when total qty of that item was 8 in order or simply enter the desired value without colon.

v) After these steps, get back to order list and split the order you have added split ratio to it. 

vi) Now you can split from actions dropdown or split icon in orders list against the order number in row.

= Emails | Payments =

**&#10048; 1. Will the split have happened after the payment is made?**

Split has nothing to do with paid or unpaid orders. It will obey your rules, if you will set rules for processing, on-hold or even completed orders, it will trigger split action on time. It has to split only; it has nothing to do with stripe or PayPal difference. It will clone the payment records form parent order to child orders.

**&#10048; 2. Will splitting run the hooks too to send out the emails to my warehouses?**

If you are already triggering something with WooCommerce order status updated hooks, your hooks will remain intact. This plugin will simply trigger it's own functions, so you can say, an order processed and moved from on-hold to processing status. Your custom hooks and this plugin's hooks will work together according to the priorities set. About emails to your warehouse, you need to check if your emails related hooks are there, yes it will be working automatically.

**&#10048; 3. Using WCFM, when email and PDF attachment sent, how does it work?**

This plugin will split your parent order into multiple child orders. Each order will have separate vendor or group of vendors products together. According to that splitted order, PDF invoice can be regenerated and emails can be sent.

i) You can leave selection of vendors so it will consider all vendors to be in separate orders with their products

ii) If selection made, then vendors can be grouped together 

e.g. 

Vendor A & Vendor B = Group #1
Vendor C & Vendor D = Group #2

iii) After split emails can be sent to users, admin and even vendors. There is a checkbox available on emails tab, you can check that so instead of admin, vendors will receive the emails.

iv) Easily create custom order statuses and trigger custom emails when order status changes.


== Screenshots ==
1. Compatibility List
2. Default Mode - Explanation
3. Exclusive Mode - Explanation
4. Inclusive Mode - Explanation
5. Shredder Mode - Explanation
6. In Stock / Out of Stock Mode - Explanation
7. Quantity Split Mode - Explanation
8. Category Based Mode - Explanation
9. Grouped Categories Mode - Explanation
10. Grouped Products Mode - Explanation
11. Group by Attributes - Explanation
12. Category Based Quantity Split
13. Order Page
14. WooCommerce Orders List
15. WooCommerce Orders List > Split & Clone Icons
16. Order Page > Selective Products
17. WooCommerce Orders List > "Split From" column added [Premium Feature]
18. Settings page > "Automatic Settings" [New Feature]
19. Settings page > Rules [Premium Feature]
20. Automatic Settings > Illustration [Visual Aid]
21. Manual Split Option
22. Consolidate/Merge/Combine
23. PPOM compatibility - Quantity Split Mode
24. Notices and Customization
25. Labels and Automatic Settings
26. Emails Tab - Child Page Labels - SMTP - Test Email
27. Email Logs
28. Troubleshoot Tab
29. Import/Export Settings
30. Group by Attributes - At a Glance
31. Screen Options
32. Group by Attributes Values - At a Glance [Visual Aid Explained]
33. Split Overview on Checkout Page [New Feature]
34. Compatibility List
35. Settings page
36. press "Save Changes" to proceed with new selected method
37. Different ways to apply shipping charges
38. Order total based shipping charges criteria
39. Custom Order Statuses (New Feature)
40. Compatibility with WooCommerce PDF Invoices & Packing Slips > PDF Invoice
41. Compatibility with WooCommerce PDF Invoices & Packing Slips > PDF Slip
42. Compatibility with WooCommerce Product Vendors
43. Split by Vendors (Terms)
44. Group by Vendors - Explanation
45. Change status of every parcel.
46. Screen options for split methods.
47. Group by ACF Field Values.
48. Empty parent order status & rules for parent order in single order case.
49. Group items by date and payment type.
50. Backorder Automation > change status of back-order status upon stock updating.
51. Coupon without restrictions.
52. Coupon with restrictions like order minimum 3 items to get this coupon work.
53. Subscription Split (This option will split all items with single quantity in each order with selected delivery date.)
54. Update status for WCFM Front-end Manager.
55. Subscription date will be updated accordingly
56. Subscription split > Settings Page
57. New Split Method Introduced: Group by Order Item Meta Values (Example: Stock Locations for WooCommerce)
58. Group by Order Item Meta Values
59. Assign a shipping class to a product under shipping section using Edit Product page.
60. Assign a shipping class to a category using Edit Category page.
61. Set status to hide parent order from admin and vendors.
62. Gravity Forms - Fields Selection
63. Gravity Forms - Group by metadata collected from product page during order
64. Grouped Categories Mode + WooCommerce Ship to Multiple Addresses
65. Order statuses with Background + Text color selection

== Changelog ==
= 5.2.9 =
* New: Group by Vendors (User Terms) can be used with select all options of upcoming new vendors. [18/08/2024][Thanks to Yaniv]

= 5.2.8 =
* Fix: Function wc_os_get_order_meta() updated. [06/06/2024][Thanks to Steve Senella]
* Fix: In stock/out of stock related stock bottleneck will be considered if stock value is exactly zero. [10/07/2024][Thanks to Shihab Rahman]
* New: In stock/out method will use benefits of the following action hook to get the current stock values of products and cart items woocommerce_checkout_order_created. [29/07/2024][Thanks to Shihab Rahman]

= 5.2.7 =
* Fix: Error message: Uncaught Error: Call to a member function get_edit_order_url() on bool. [04/06/2024][Thanks to Russ]

= 5.2.6 =
* Fix: Split overview on checkout page feature reinstated. [04/06/2024]

= 5.2.5 =
* Fix: Edit order URL with the latest WooCommerce order object function. [03/06/2024][Thanks to Russ]

= 5.2.4 =
* Fix: Compatibility added for both "po_number" and "_po_number". [02/06/2024][Thanks to Fruitfull Offices]

= 5.2.3 =
* Fix: Uncaught ArgumentCountError: Too few arguments to function wc_os_order_splitter::wc_os_orders_list_columns_content(). [02/06/2024][Thanks to Ido Kobelkowsky]

= 5.2.2 =
* Fix: Uncaught ArgumentCountError: Too few arguments to function wc_os_order_splitter::wc_os_orders_list_columns_content(). [02/06/2024]

= 5.2.1 =
* Fix: Uncaught ArgumentCountError: Too few arguments to function wc_os_order_splitter::wc_os_orders_list_columns_content(). [01/06/2024][Thanks to Steve Senella]

= 5.2.0 =
* New: woocommerce_order_query_args related filter hook used for parent-order child-order list table. [01/06/2024]

= 5.1.9 =
* Fix: Edit order page, split order option improved. [01/06/2024][Thanks to Steve Senella]

= 5.1.8 =
* Fix: Edit order page, split order option improved. [01/06/2024][Thanks to Steve Senella]

= 5.1.7 =
* Fix: Bulk edit merge option improved. [27/05/2024][Thanks to Steve Senella]

= 5.1.6 =
* Fix: Backorder limit related improvements. [08/05/2024][Thanks to Steve Senella]

= 5.1.5 =
* Fix: Cron related update query. [26/04/2024][Thanks to Devon Jordaan]

= 5.1.4 =
* Fix: Fatal error: Uncaught Error: Call to undefined function pre(). [26/04/2024][Thanks to Izabela Reis Peres | Diogo Freitas]

= 5.1.3 =
* Fix: HPOS related post_type handled within the cron section. [26/04/2024][Thanks to Devon Jordaan]

= 5.1.2 =
* Metadata related function improved with the filter hook "wc_os_orders_meta_keys_to_string". [24/04/2024][Thanks to Russ]

= 5.1.1 =
* Fix: Fatal error: Uncaught Error: Call to a member function get_status() on bool. [23/04/2024][Thanks to Diogo Freitas / Gerente de Produtos]

= 5.1.0 =
* Metadata related function improved with the WordPress function maybe_unserialize(). [17/04/2024][Thanks to Russ]

= 5.0.9 =
* Remove Original/Parent Orders related settings added on the settings page. [16/04/2024][Thanks to Russ]

= 5.0.8 =
* Consolidate/Combine/Merge Orders related settings added on the settings page. [16/04/2024][Thanks to Russ]
* https://wordpress.org/support/topic/fatal-error-on-place-order-2/. [13/04/2024][Thanks to @asarda]

= 5.0.7 =
* HPOS enabled and proceed to checkout related fix when auto split is OFF. [05/04/2024][Thanks to Chandirasekaran.S]

= 5.0.6 =
* $wc_os_attributes_nodes and member function get_id() on bool related fixes. [04/04/2024][Thanks to Tobias Derksen]

= 5.0.5 =
* Downward compatibility ensured. High-Performance Order Storage (HPOS) - WooCommerce [03/04/2024]

= 5.0.4 =
* added bulk_actions-woocommerce_page_wc-orders https://stackoverflow.com/questions/77366037/filtering-orders-list-in-woocommerce-with-hpos [02/04/2024]

= 5.0.3 =
* Merge order feature beefed up with more meta keys which should not be dealt as an array anymore. [28/02/2024]

= 5.0.2 =
* Clone order feature tested and refined. [26/02/2024]

= 5.0.1 =
* Improved: Updated the split orders limit in queue to 24 from 6. [28/01/2024]
* Fix: I/O split method improved from the backorder split again on stock availability. [30/01/2024][Steve Senella]
* Emails section tested and revised. [18/02/2024][Ido Kobelkowsky]
* Emails section improved and troubleshooting made easy. [23/02/2024][Andrew / leiamoon.com]
* Parent order without taxes will produce the child orders without taxes too. [23/02/2024][Jonathan Kraft]

= 5.0.0 =
* Revised: Updated the split orders limit in queue to six from one. [28/01/2024]


== Upgrade Notice ==
= 5.2.9 =
New: Group by Vendors (User Terms) can be used with select all options of upcoming new vendors.
= 5.2.8 =
Fix: Function wc_os_get_order_meta() updated.
= 5.2.7 =
Fix: Error message: Uncaught Error: Call to a member function get_edit_order_url() on bool.
= 5.2.6 =
Fix: Split overview on checkout page feature reinstated.
= 5.2.5 =
Fix: Edit order URL with the latest WooCommerce order object function.
= 5.2.4 =
Fix: Compatibility added for both "po_number" and "_po_number".
= 5.2.3 =
Fix: Uncaught ArgumentCountError: Too few arguments to function wc_os_order_splitter::wc_os_orders_list_columns_content().
= 5.2.2 =
Fix: Uncaught ArgumentCountError: Too few arguments to function wc_os_order_splitter::wc_os_orders_list_columns_content().
= 5.2.1 =
Fix: Uncaught ArgumentCountError: Too few arguments to function wc_os_order_splitter::wc_os_orders_list_columns_content().
= 5.2.0 =
New: woocommerce_order_query_args related filter hook used for parent-order child-order list table.
= 5.1.9 =
Fix: Edit order page, split order option improved.
= 5.1.8 =
Fix: Edit order page, split order option improved.
= 5.1.7 =
Fix: Bulk edit merge option improved.
= 5.1.6 =
Fix: Backorder limit related improvements.
= 5.1.5 =
Fix: Cron related update query.
= 5.1.4 =
Fix: Fatal error: Uncaught Error: Call to undefined function pre().
= 5.1.3 =
Fix: HPOS related post_type handled within the cron section.
= 5.1.2 =
Metadata related function improved with the filter hook "wc_os_orders_meta_keys_to_string".
= 5.1.1 =
Fix: Fatal error: Uncaught Error: Call to a member function get_status() on bool.
= 5.1.0 =
Metadata related function improved with the WordPress function maybe_unserialize().
= 5.0.9 =
Remove Original/Parent Orders related settings added on the settings page.
= 5.0.8 =
Consolidate/Combine/Merge Orders related settings added on the settings page.
= 5.0.7 =
HPOS enabled and proceed to checkout related fix when auto split is OFF.
= 5.0.6 =
$wc_os_attributes_nodes and member function get_id() on bool related fixes.
= 5.0.5 =
Downward compatibility ensured, but please do not update if everything is working fine. This update is about HPOS related changes. High-Performance Order Storage (HPOS) - WooCommerce
= 5.0.4 =
High-Performance Order Storage (HPOS) - WooCommerce
= 5.0.3 =
Merge order feature beefed up with more meta keys which should not be dealt as an array anymore.
= 5.0.2 =
Clone order feature tested and refined.
= 5.0.1 =
Improved: Updated the split orders limit in queue to 24 from 6.
= 5.0.0 =
Revised: Updated the split orders limit in queue to six from one.

== License ==
This WordPress plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. This WordPress plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this WordPress plugin. If not, see http://www.gnu.org/licenses/gpl-2.0.html.