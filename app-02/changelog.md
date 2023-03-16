# Changelog

All notable changes to this project will be documented in this file

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 5.0.x - 2022-xx-xx

### Added

- [#GNDR-6705] - Remove functionality related to Onboarding (#GNDR-5604, #GNDR-5605)
- [#GNDR-7261] - Create order cancel - declined endpoint
- [#GNDR-7293] - Cancel request call to action
- [#GNDR-7303] - Quote file required cases
- [#GNDR-7312] - Deprecate orders/log in Live V2 and allow empty request Type in orders in Live V2
- [#GNDR-7289] - Decline Request call to action
- [#GNDR-7520] - Remove notification reminder to the orders with sub status quote needed
- [#GNDR-7242] - New Total modal
- [#GNDR-7330] - Update button call to action
- [#GNDR-7318] - Add endpoint to update initial Item Order requested quantity
- [#GNDR-7258] - Extra items endpoints update and order index and detailed refactor
- [#GNDR-7054] - Update price_and_availability and will_call_and_approved values in firebase
- [#RIVEN-639] - Create table for truck stock
- [#GNDR-7095] - Post confirmation status items detailed view adjustments
- [#GNDR-7088] - Add custom item by supplier
- [#GNDR-6859] - Order status - Curri - Completion
- [#GNDR-7091] - Send button + Preview and Send pop-up
- [#GNDR-7098] - Endpoint to update total order - V4
- [#GNDR-6930] - Add endpoint to update Item Order requested quantity
- [#GNDR-7036] - "Sending as" dropdown
- [#GNDR-7109] - create endpoint to set assigne and update order status
- [#GNDR-6932] - Send Pubnub new message endpoint to V4
- [#GNDR-7008] - Enter Total section
- [#GNDR-6856] - Support order status pick up completion
- [#GNDR-7249] - Prepare Redis support
- [#RIVEN-569] - Saving brand and custom model for support call
- [#GNDR-6743] - Full Confirmation screen - Quote/Invoice Preview
- [#GNDR-7011] - Load Quote/Invoice file
- [#RIVEN-624] - Add last_message_at to supplier/users endpoint
- [#GNDR-6873] - Add Part resource & endpoint in live V2
- [#GNDR-6927] - Log List of Orders in live V2
- [#GNDR-6740] - Edit Job/Reference Name Modal
- [#GNDR-6746] - Add supplierUser info to orders endpoint
- [#GNDR-7062] - Create a V2 fallback for V1 endpoints
- [#GNDR-7094] - Approve shipment endpoint
- [#GNDR-6714] - Truck Stock addition ability - Intermediate Cart
- [#RIVEN-567] - Track items removed from cart
- [#GNDR-6720] - Add part and supply & custom items endpoints in live V2
- [#RIVEN-406] - Update profile controller removing company and change company create list and update to V4
- [#RIVEN-567] - Track items removed from cart
- [#GNDR-6588] - Add order cancel endpoints in V4
- [#GNDR-6855] - Adjust the delivery request validations
- [#GNDR-6645] - This is for personal flow toggle
- [#GNDR-6539] - Add active orders endpoint in v4
- [#GNDR-6711] - Truck Stock addition ability - Categories and Subcategories flow for V4
- [#GNDR-6651] - Update supplier_total_order_node value in firebase realtime database
- [#GNDR-6657] - Order Hub - List of Orders
- [#GNDR-6766] - Pick up confirmation
- [#GNDR-6528] - Active Requests & Order banner - Status Adjustments - Blue & Red Formats
- [#GNDR-6532] - Active Requests & Orders banner - Green format
- [#GNDR-6763] - Add details to show order endpoint in v4
- [#GNDR-6542] - Order status adjustments + Redirections
- [#GNDR-6333] - Create Approve order endpoint in V4
- [#GNDR-6580] - Custom item V4
- [#GNDR-6463] - Change status field to status_id on orders
- [#RIVEN-551] - Add verified field to basecamp supplier resource
- [#RIVEN-543] - Fix basecamp users endpoint to work with deleted users
- [#GNDR-6556] - Delivery address endpoint
- [#GNDR-6787] - Item order adjustments v4
- [#GNDR-6554] - Create order deliver endpoint in V4
- [#GNDR-6760] - Cart item bulk store
- [#GNDR-6523] - Add endpoint Order store v4
- [#GNDR-6687] - Default supplier and cart endpoints to v4
- [#GNDR-4737] - Upgrade PHP to version 8.1
- [#GNDR-6704] - Add search string to Supply endpoint
- [#GNDR-6244] - Delivery Method Chooser
- [#GNDR-6466] - Add FK substatus_id on order_substatus table
- [#GNDR-6465] - Support Statuses and Substatuses
- [#GNDR-6464] - Refactor statuses to order_substatus
- [#GNDR-6522] - Add state and zip_code to V3/Order/SupplierResource
- [#GNDR-6547] - Add v2 live and v4 mobile route bases
- [#GNDR-6291] - Add supply category in Supply resource
- [#GNDR-6399] - Add note to the order
- [#GNDR-6375] - Tie store selection to the cart
- [#RIVEN-357] - Basecamp - inbound queue call
- [#RIVEN-373] - Search name in History log
- [#GNDR-6363] - Supply subcategories search
- [#RIVEN-388] - Basecamp - api authentication
- [#GNDR-6111] - Active Requests & Orders Banner
- [#GNDR-6004] - Supply endpoint with search and sort by lasted added to the cart
- [#GNDR-6003] - Update sort the part list
- [#GNDR-6002] - Update sort oem list
- [#GNDR-6001] - Hide part number in v3 parts index endpoint
- [#GNDR-5975] - Add endpoint list sorted grouped supplier
- [#GNDR-5899] - Bluon user info for call history log
- [#GNDR-5996] - Hide number
- [#RIVEN-359] - Basecamp - user information endpoint and user suppliers endpoint
- [#GNDR-5852] - Hide part number
- [#GNDR-6019] - Add field Status on Curri Delivery resource
- [#GNDR-6030] - Hide part number in v3 part controller for specific oem. Refactor function into resource
- [#GNDR-5883] - Default Store Selection logic
- [#GNDR-5953] - Hide part number in v3 part controller for specific oem
- [#GNDR-5434] - Log profile activity
- [#GNDR-5398] - Last supplies added to cart
- [#GNDR-5397] - Last viewed parts by user
- [#GNDR-5749] - Add endpoint user supplier orders
- [#GNDR-5125] - Delete system model
- [#RIVEN-353] - Tech support call initialite from model home
- [#GNDR-5454] - Search Activity by log_name column
- [#GNDR-5901] - Testing data oems support
- [#GNDR-5433] - Log orders activity
- [#GNDR-5605] - Verification endpoint
- [#GNDR-5604] - User registration endpoint
- [#GNDR-5432] - Update forum activity log name to forum
- [#GNDR-5385] - Most searched brands endpoint
- [#GNDR-5393] - Support brand hits
- [#GNDR-5835] - Add distance to supplier resource
- [#GNDR-5755] - Refactor supplier detailed resource
- [#GNDR-5692] - Validation max wishlist items
- [#GNDR-5337] - Add new information to resource
- [#GNDR-5316] - Update wishlist endpoint
- [#GNDR-5317] - Delete wishlist endpoint
- [#GNDR-5315] - Create wishlist endpoint
- [#GNDR-5251] - Creation/edition of Resources Note and NoteCategory
- [#GNDR-5377] - Endpoint Wishlist create, update and delete
- [#GNDR-5252] - Endpoint to list notes and change endpoint to show note
- [#GNDR-5254] - Mark as read endpoint
- [#GNDR-5253] - Endpoint Notification list endpoint
- [#GNDR-5216] - Endpoint Wishlist item list
- [#GNDR-5250] - Support for NoteCategory model
- [#GNDR-5215] - Wishlist list endpoint
- [#GNDR-5214] - Support for Wishlist

### Fixed

- [#GNDR-7418] - Added filter by On The Network
- [#GNDR-7587] - Company account attribute added to order base resource
- [#GNDR-7388] - Added increment on brand detail counter
- [#GNDR-7419] - Part Numbers Are Hidden in Parts Search
- [#GNDR-7407] - Store Changes Do Not Show in Cart
- [#GNDR-7033] - Banner is not showing correctly
- [#GNDR-6819] - Fix end dates validation
- [#GNDR-6449] - Fix Open orders number shows the total amount of orders
- [#GNDR-6169] - Post creation endpoint fail when an image was attached

## 4.1.1 - 2023-xx-xx

### Added

- [#RIVEN-570] - Update title in push notification for chat new message
- [#RIVEN-603] - Zero Points on SMS Text for Quotes approval reminder
- [#RIVEN-604] - New logic blocking tech support

## 4.1.0 - 2023-02-21

### Added

- [#RIVEN-587] - Fix foreign keys details counter
- [#BL-116]    - Add Memex iframe in Nova
- [#RIVEN-533] - Update flag control
- [#RIVEN-487] - Call support button point validation
- [#RIVEN-159] - Remove see_below_item status from item order update endpoint
- [#RIVEN-477] - Pagination added to users by post endpoint
- [#RIVEN-476] - Pagination added to users endpoint
- [#RIVEN-412] - Relationship between (model and part) search counter tables and details tables
- [#RIVEN-403] - Search Company by name endpoint.
- [#RIVEN-405] - Company creation endpoint.
- [#RIVEN-292] - Restrict curri at the store level
- [#RIVEN-121] - Take Snaps of user actions.

### Fixed

- [#RIVEN-572] - Tech support is still blocked even if User has more than 1000 points already
- [#RIVEN-553] - Sort the xoxo vouchers

## 4.0.2 - 2023-02-09

### Added

- [#RIVEN-503] - Add replacement to item order list
- [#RIVEN-499] - Order automatic notifications for one week

## 4.0.1 - 2023-01-31

### Added

- [#RIVEN-462] - Replicate operation list part of V3 in V1
- [#RIVEN-439] - Nova > Legacy > Users tab not loading

## 4.0.0 - 2023-01-24

### Added

- [#RIVEN-386] - Update authentication for hubspot
- [#RIVEN-383] - Delete removed vouchers on sync command
- [#RIVEN-315] - Default screen even after quote is already sent to techs first request. Order by newest.
- [#RIVEN-306] - Update curri delivery quote when address is updated
- [#GNDR-5907] - Remove code for "Block users though Nova" functionality
- [#RIVEN-305] - Add new parameters on Xoxo redeem email
- [#RIVEN-233] - Add new line items modifications. Remove required fields
- [#GNDR-5874] - Remove open value type vouchers
- [#GNDR-5874] - Fix phone redeem xoxo voucher
- [#GNDR-4920] - Endpoint for place Xoxo order Redeem
- [#RIVEN-156] - Tech support call - Last visited Model
- [#RIVEN-240] - Curri move "accounting email" to "customerData" and remove "tech phone" from "dropOffNotes"
- [#RIVEN-209] - Create other delivery type
- [#RIVEN-239] - Add call_group_tags and calling_groups columns to the oems table
- [#RIVEN-251] - Change the hour store format
- [#GNDR-5451] - Deprecate UpdateTypeAction and change it to OrderDeliveryHandler
- [#RIVEN-122] - Activity log item order
- [#GNDR-5452] - Deprecated middleware change time format for order delivery
- [#GNDR-4921] - Implement XOXO service request log
- [#GNDR-4757] - Track when user loads Truck Stock screens
- [#GNDR-4760] - Send Pubnub message
- [#RIVEN-100] - Add video url to post show endpoint
- [#GNDR-3502] - Deprecate system model
- [#GNDR-4919] - Selected Redeem Option screen
- [#GNDR-4675] - Update oems migration
- [#GNDR-5161] - Compliance to Onboarding screen form request
- [#GNDR-5119] - Schedule a command to update xoxo tokens
- [#GNDR-4666] - New endpoint to list the xoxo vouchers
- [#GNDR-4674] - BE - Technical Debt - Update contactors and compressors migration
- [#GNDR-4667] - Points redeemed history
- [#GNDR-4665] - Xoxo integration service
- [#GNDR-4916] - Paginate the list of user, when looking for tagged users in a specific post
- [#RIVEN-181] - Store full search input
- [#RIVEN-208] - Rename distributor delivery to warehouse delivery
- [#RIVEN-106] - Modify text on sms notifications
- [#RIVEN-70]  - Searching for someone’s personal name on forum should respond their forum name
- [#RIVEN-19]  - Business hours should be selected in a logical range of time
- [#RIVEN-21]  - it is necessary to save the original cart when the order is created
- [#GNDR-5277] - Update cache table creation migration
- [#GNDR-5239] - Remove attributes from cart item resource
- [#RIVEN-43]  - Allow to upload at least 5 photos on forum post
- [#GNDR-5211] - Delayed Jobs with same ETA issue fix
- [#RIVEN-184] - Check existence of cart before trying delete
- [#GNDR-5344] - Remove autocomplete for curri orders
- [#GNDR-5334] - Update forbidden zip codes file without duplicates
- [#GNDR-5258] - Populate curri forbidden zip codes database
- [#GNDR-5212] - Remove validation on "Confirm - Book Delivery" button
- [#RIVEN-12]  - Add secondary sorting criteria for oems list
- [#GNDR-5190] - Remove firebase node when order is canceled
- [#GNDR-5200] - Fix time format in some notifications and popups
- [#RIVEN-28]  - Support for Curri forbidden zip codes
- [#GNDR-5179] - Validate price zero on curri delivery
- [#GNDR-5019] - ETA Format Change - Date & Hour Range split
- [#GNDR-5106] - Fix time format migration
- [#GNDR-5097] - Fix realtime supplier key and supplier confirmation attribute added to resource
- [#GNDR-5093] - Clean fee on pickup
- [#GNDR-4934] - CurriDelivery OnRoute sms and push notifications
- [#GNDR-4733] - Add popUp supplier information on firebase
- [#GNDR-4784] - Quotes/Chats standardize end points on Logistics section
- [#GNDR-5059] - Add can use curri delivery to v1 supplier endpoint
- [#GNDR-4754] - Delivery has arrived Notification
- [#GNDR-5034] - Delay booking when the datetime is edited
- [#GNDR-5011] - BD-158 Add customer data to curri request
- [#GNDR-4342] - Send curri on route pubnub message to user
- [#GNDR-4651] - Validate the list of forbidden zip code do not work with curri delivery when the order is created
- [#GNDR-4648] - Update resource and form-request with list of forbidden zip codes
- [#GNDR-4987] - Remove migrations for columns added in the process of airtable to digital ocean migration
- [#GNDR-4807] - Support Curri webhook
- [#GNDR-4732] - Add supplier information on Real Time Database and Curri delivery services
- [#GNDR-4142] - New endpoint Update delivery data, price, address
- [#GNDR-4731] - Confirm Delivery Details Popup Notifications
- [#GNDR-4954] - Support for show when a comment text was updated
- [#GNDR-4434] - Save tech's company address in DB during onboarding
- [#GNDR-4729] - Update Confirm Delivery Details Popup trigger
- [#GNDR-4272] - Save tech's company address in DB during onboarding
- [#GNDR-4624] - Endpoint to update/book Curri delivery in V3
- [#GNDR-4647] - Add a list of zip codes do not allow with curri
- [#GNDR-4498] - Add cart management endpoints
- [#GNDR-4756] - Calculate Price in Curri delivery
- [#GNDR-4339] - Create a new endpoint to confirm delivery address
- [#GNDR-4752] - Address 2 field added to realtime database
- [#GNDR-4341] - Changes related with BD-146
- [#GNDR-4176] - Changes related with BD-150
- [#GNDR-4431] - pending_orders_count and pending_approval_orders_count keys added to LiveApi\V1\Supplier\BaseResource
- [#GNDR-4428] - Cart Tracking support
- [#GNDR-4143] - Update the ETA from delivery
- [#GNDR-4132] - Move notification settings endpoint to settings
- [#GNDR-4119] - Create new setting and update bid number validation
- [#GNDR-4441] - Add parts_manuals column in oems table
- [#GNDR-4439] - Updates to Invoices csv file
- [#GNDR-4110] - In-app Notification when Suppliers update ETA for a Quote
- [#GNDR-4042] - Endpoint to update delivery type
- [#GNDR-4043] - New endpoint to update eta time/date from order delivery
- [#GNDR-4041] - Support and refactor order_deliveries table
- [#GNDR-4187] - Update eta validation - outdated
- [#GNDR-4404] - State and country added to UserResource
- [#GNDR-4034] - Support call categories nova resource
- [#GNDR-4036] - Support call endpoint updated to work with support call categories
- [#GNDR-4037] - Support call categories and subcategories endpoints
- [#BD-151] - Calculate delivery price
- [#GNDR-3397] - Send notifications to tagged users in posts/comments
- [#GNDR-4234] - Update the script to process brands and series table
- [#GNDR-4038] - Support for new at_# columns and copy current values to the new columns
- [#GNDR-4039] - Update script to save SQL script with the changes made to the tables
- [#GNDR-4188] - Add store image to resource
- [#GNDR-4033] - Update Tech Support First screen Options
- [#GNDR-2992] - Add terms column on suppliers table
- [#GNDR-3818] - New fields added on Order/OemResource
- [#GNDR-3574] - Creator added to CustomItemResource
- [#GNDR-3587] - New endpoint to store bid number and availability and update send for approval controller
- [#GNDR-3590] - Orders list ordered by created_at and total items added on every order

### Fixed

- [#RIVEN-417] - Activity Section of Profile shows Nonsense information
- [#RIVEN-387] - Fix post creation with heic image
- [#RIVEN-375] - Fix migration process to update deliveries and fix command model types
- [#GNDR-5968] - Curri integration, "Confirm Delivery Details"
- [#RIVEN-339] - Curri Zip codes restriction fix
- [#RIVEN-321] - Modify ModelType Seeder
- [#GNDR-5879] - Fix phone redemption
- [#GNDR-5867] - Fix Calculate Price curri. Origin Address returns null
- [#GNDR-5286] - Tech name not passed to curri
- [#GNDR-5193] - Change trigger pop up tech
- [#GNDR-5104] - Fix Techs cant send quotes to suppliers with restricted ZIP codes
- [#GNDR-5201] - Fix time curri book delivery
- [#GNDR-4781] - Use exceptions to handle Curri errors
- [#GNDR-4625] - Add validation to the date field and transaction

## 3.9.0 - 2022-11-28

### Added

- [#GNDR-4841] - Store seconds spent by user watching the Video
- [#GNDR-4502] - Support video url and logic for requires confirm
- [#GNDR-4690] - Update message when a Quote Request is created outside working hours
- [#GNDR-4455] - App version resource added to account resource
- [#GNDR-4501] - Add visible_at field in supply_categories in nova
- [#GNDR-4440] - Add "Sort" column in "Unit Type" screen, Model_type table
- [#GNDR-4500] - Add visible_at column in supply_categories table
- [#GNDR-4499] - Add new branch timezone
- [#GNDR-4413] - Add title/video to app version resource
- [#GNDR-4411] - Add PopUp with Video
- [#GNDR-4412] - Endpoint for storing the timestamp when the user close the popup
- [#GNDR-4035] - Instrument nova resource
- [#GNDR-4034] - Support call categories nova resource
- [#GNDR-4036] - Support call endpoint updated to work with support call categories
- [#GNDR-4037] - Support call categories and subcategories endpoints

### Fixed

- [#GNDR-4332] - Add layout version to seeder
- [#GNDR-4468] - Supplies seeder throws an exception

## 3.8.3 - 2022-11-08

### Fixed

- [#GNDR-4699] - Fix date logging users api usage

## 3.8.2 - 2022-11-07

### Added

- [#GNDR-4590] - Create a new field to store timestamp in the api-usage

## 3.8.1 - 2022-11-01

### Added

- [#GNDR-3963] - Tracking when User loads Supplier Selection Screen
- [#GNDR-3962] - Log API usage by user/supplier

### Fixed

- [#GNDR-4479] - Fix duplicate API usage log
- [#GNDR-4443] - Supplier Selection Screen - Tracking when User loads Supplier Selection Screen

## 3.8.0 - 2022-10-27

### Added

- [#GNDR-4300] - Set hat_requested as true when a User is verified from Nova
- [#GNDR-3395] - New field on Post for YouTube embedded
- [#GNDR-3505] - Reincorporate remove points listener and add logic to remove points when item is removed
- [#GNDR-2936] - Bulk brand validation
- [#GNDR-3826] - Command to create credit for canceled orders with processed invoice and without credit
- [#GNDR-3829] - Subcategories routes fixed to allow infinite nesting
- [#GNDR-3828] - Supply type removed
- [#GNDR-3936] - Tag users on comment creation-edition
- [#GNDR-3384] - New endpoint to display recently viewed oems and parts
- [#GNDR-3827] - Generate Credit for invoiced orders that gets Cancelled
- [#GNDR-3825] - Support order invoices, type and processed_at fields added
- [#GNDR-3935] - New endpoint to search user by full name or public name
- [#GNDR-3501] - Sorting to supply categories endpoints
- [#GNDR-3500] - Sort column to supply categories in nova admin pages
- [#GNDR-3934] - New endpoint to search user related to post
- [#GNDR-3498] - Add new 'internal_name' column on Common items table
- [#GNDR-3496] - Email notification Redesign
- [#GNDR-3583] - Add accept endpoint T&C and update logic of user tos_accepted
- [#GNDR-3849] - Show public name on Forum instead of real name
- [#GNDR-3868] - Update Approved By Team push notification title
- [#GNDR-3580] - Order points earned notifications created
- [#GNDR-3797] - Allow selection for non-visible suppliers for the user
- [#GNDR-3581] - Notifications updated to add earning and loosing Bluon points
- [#GNDR-3820] - Allow edition of new post types without tags
- [#GNDR-3582] - Add support T&C and Nova
- [#GNDR-3816] - Update approve by team notifications text.
- [#GNDR-3817] - Add Refrigeration to model types seeder
- [#GNDR-3558] - Save supplier endpoint
- [#GNDR-3385] - Add saved models endpoint
- [#GNDR-3503] - App settings for video URLs
- [#GNDR-3396] - Forum view other users profile
- [#GNDR-3393] - Notifications to Technician when quote is approve from web link
- [#GNDR-2890] - On delete user assign custom item to supplier order
- [#GNDR-2858] - Add endpoint to delete custom_items
- [#GNDR-2889] - Add polymorphism fields into custom_item

### Fixed

- [#GNDR-4376] - Repeated items on part list pagination
- [#GNDR-4226] - Search by User FullName and PublicName on forum
- [#GNDR-4227] - Video labels in nova
- [#GNDR-4141] - Push and in-app notifications show "bid" instead of "po" number
- [#GNDR-4161] - Canceled Order notification shows 0 points lost

## 3.7.3 - 2022-10-11

### Fixed

- [#GNDR-4087] - Order sent for approval notification showing 0 points

## 3.7.2 - 2022-10-10

### Fixed

- [#GNDR-4031] - Pending approval Quotes reminder notification showing 0 points

## 3.7.1 - 2022-10-07

### Added

- [#GNDR-3964] - Log oems and part searches in api/v3 and live-api/v1
- [#GNDR-3968] - Earning and Loosing Bluon Points Notifications update
- [#GNDR-3991] - Stores image inconsistency
- [#GNDR-3948] - Set Visible by user field checked by default

## 3.7.0 - 2022-10-04

### Added

- [#GNDR-3733] - Add line showing “Closes Soon“, “Opens Tomorrow“ or “Open until“ on the Supplier Selection Screen
- [#GNDR-3643] - Modify Oem FKs delete on cascade
- [#GNDR-3504] - Link Text added on Note
- [#GNDR-3483] - Create the supplier_user entry to be seen by Supplier only
- [#GNDR-3492] - Add points data to resource
- [#GNDR-3568] - Fix forum delay on post creation and post solved
- [#GNDR-3506] - Nova Account tab Notifications number fix
- [#GNDR-3341] - Tracking support calls
- [#GNDR-1979] - Add user disabled validation to in app notifications
- [#BL-61] - Add edit button to customers dashboard
- [#GNDR-3118] - Add sort field on nova supply resource
- [#GNDR-3543] - Add Boiler to model types seeder
- [#GNDR-3244] - Add Nova ability to make entry in the Bluon Points table
- [#GNDR-2975] - Add verified attribute to user resource in post detail/list
- [#GNDR-3528] - Fix change endpoint working hours data
- [#GNDR-3369] - Change pending approval reminder default setting to true
- [#GNDR-3117] - Add sort column on supplies table
- [#GNDR-3114] - Endpoint tos recommend replacements
- [#GNDR-3394] - Add "BluonLive" tag when selecting Supplier "On the network"
- [#GNDR-3113] - Support for recommended replacements
- [#GNDR-3110] - Add "Working on it" notification to Panel
- [#GNDR-3161] - Support brand parts
- [#GNDR-2990] - Search capacity in store selection
- [#GNDR-2843] - Store when Bluon user first view a "Pending approval" order in Request Summary screen
- [#GNDR-3108] - Show user by channel endpoint added
- [#GNDR-3127] - Add new scope and order by lat log on account supplier list
- [#GNDR-2977] - Allow mark/un-mark best answer by admin
- [#GNDR-3181] - Order pending approval reminder user timezone null
- [#GNDR-3159] - Add toggle configuration in Update endpoint
- [#GNDR-2855] - Add supplier open hours on supplier lists
- [#GNDR-2720] - Add store's address and city to supplier resources
- [#GNDR-2842] - Store when the Share quote button is clicked
- [#GNDR-2714] - Order pending approval reminder in scheduler
- [#GNDR-2841] - Auto update timezone on each update of the state/country/zip
- [#GNDR-2894] - Endpoint to search by part number
- [#GNDR-2893] - Endpoint to search by model
- [#GNDR-2728] - Add toggle configuration in Nova
- [#GNDR-2719] - Logic for send notifications
- [#GNDR-1917] - Support for HEIC images
- [#GNDR-2712] - Order pending approval reminder notification (in-app and sms)
- [#GNDR-2718] - Set up toggle notification
- [#BL-63] - List, remove and restore unconfirmed users
- [#BD-141] - Save OEMs functionality
- [#GNDR-2840] - Timezone added to user model and resources
- [#GNDR-2887] - Add solver user data in post list
- [#GNDR-2861] - Allow post creation without tags for type funny or needs-help
- [#GNDR-2815] - Forum Up-voting Post
- [#GNDR-2816] - Forum Pinning a Post
- [#GNDR-2739] - Add filter by type option to posts list
- [#GNDR-2738] - Create new column type for posts table and save type on post creation

### Fixed

- [#GNDR-3792] - Parts & Models Pagination
- [#GNDR-3569] - Quote/Supplier incorrect sort when a technician sends a message
- [#GNDR-3340] - Internal notification for chat message is truncated

## 3.6.3 - 2022-09-13

### Fixed

- [#GNDR-3462] - Missing supply for some items

## 3.6.2 - 2022-09-12

### Added

- [#GNDR-3446] - Move "Do not Reply" text to the end of the message in SMS

## 3.6.1 - 2022-09-07

### Fixed

- [#GNDR-3323] - Fix cash value rate

## 3.6.0 - 2022-09-07

### Added

- [#GNDR-3034] - Update automatic message sent to Tech when an Order is Approved
- [#GNDR-3148] - Update previous order point adding command
- [#GNDR-3147] - Earn Bluon Points when order is Approved
- [#GNDR-3051] - Add points to completed previous orders
- [#GNDR-2533] - Airtable documents and images migration to Digital Ocean Spaces
- [#GNDR-1988] - Add new notification on comment forum
- [#GNDR-2567] - Catch contact creation exception on hubspot
- [#GNDR-2859] - Change type on automatic canceled message PubNub
- [#GNDR-2822] - Refactor to make endpoint to update SupplierUser data of unconfirmed users confirm them
- [#GNDR-2415] - Note show endpoint
- [#GNDR-2414] - Nova Note resource
- [#GNDR-2708] - Assign push notification title update
- [#GNDR-2527] - Endpoint to get App version and Nova resource to manage it
- [#GNDR-2510] - New endpoint to update SupplierUser data of unconfirmed users
- [#GNDR-2571] - Refactor calculation for last pubnub channel message
- [#GNDR-2514] - Update points earning logic coefficient
- [#GNDR-2419] - Return all items instead of only available
- [#GNDR-2610] - Apply setting to notification
- [#GNDR-2515] - Scope user list by username and user company name
- [#GNDR-2596] - Add field TimeZone on Supplier nova
- [#GNDR-2627] - Remove backup process from scheduler
- [#GNDR-2227] - Add automatic message when an order is canceled
- [#GNDR-2413] - Note model, factory, resource and migration
- [#GNDR-2308] - Add Notification sms logic to counterStaff
- [#GNDR-2307] - Nova update counter staff
- [#GNDR-2418] - Custom item creation endpoint
- [#GNDR-2568] - SettingSeeder added to DevelopmentSeeder
- [#GNDR-2238] - Fill sub_status field when an order is canceled
- [#GNDR-2089] - Automation support - Signup process
- [#GNDR-2306] - Add the new fields of settingStaff to the endpoints
- [#GNDR-2305] - Add SettingStaff table for storing sms notification toggle
- [#GNDR-2196] - Complete order after 7 days
- [#GNDR-2187] - Endpoint to display points, cash value and multiplier
- [#GNDR-2236] - Support and data migration for order statuses
- [#GNDR-2316] - Pubnub channels sorted by updated_at
- [#GNDR-2183] - Delete firebase database node entry for deleted users
- [#GNDR-2317] - Texts added to sms notifications
- [#GNDR-2315] - Updated PubNubMessagesTypes from Order to Quote
- [#GNDR-2068] - Add AddPoints/RemovePoints/Levels logic gamification
- [#GNDR-2067] - Support for level configuration
- [#GNDR-2197] - Include unavailable items on cancelled orders
- [#GNDR-2069] - Send pubnub message from back end
- [#GNDR-1985] - Disable each notification based on its setting
- [#GNDR-765] - Remove an item from an in progress order
- [#GNDR-2065] - Add field Multiplier on Nova for gamification
- [#GNDR-1644] - Add filter by search string in outbound
- [#GNDR-1903] - Availability field to Order Resource
- [#GNDR-2070] - Send push notification and internal notification when the supplier send a new chat message
- [#GNDR-1915] - Update firebase realtime DB entry
- [#GNDR-2176] - Support export invoices for deleted users
- [#GNDR-1981] - Seed settings table and validate user_setting route parameter
- [#GNDR-2066] - Add table BluonPoints for gamification
- [#GNDR-1782] - Invalidate login token on disabled users
- [#GNDR-1913] - Mark all notifications as read when listing them
- [#GNDR-1768] - Send specific login error message for disabled users
- [#GNDR-1645] - Disable the order status change notifications for deleted users
- [#GNDR-1444] - Delete Account - Orders created by Deleted Users
- [#GNDR-1767] - Enable/Disable users on nova
- [#GNDR-1445] - Add table OrdersLockedData when deleting account
- [#GNDR-1641] - In-App Notification order marked Declined/Canceled by Supplier
- [#GNDR-1763] - Update pending_approval_orders value in firebase realtime database
- [#GNDR-643] - Automatic message when a chat is created
- [#GNDR-1901] - Current orders count to users index
- [#GNDR-1766] - Accept timezone on supplier request
- [#GNDR-1640] - Push notification when the order is completed
- [#GNDR-1639] - Push notification for new quote from Supplier to be Approved
- [#GNDR-601] - Support delete user account
- [#GNDR-776] - Automatic message when a Quote is approved by Technician

### Fixed

- [#GNDR-3038] - Branch Manager Info and Accounting Contact Info sections are empty the first time the supplier updates
  its info
- [#GNDR-2993] - Duplicated users in users/chats
- [#GNDR-2918] - Supplies list repeated items
- [#GNDR-2904] - Correct the auto message which appears in the wrong side of the chat
- [#GNDR-2724] - Bug adding o removing points in orders without users
- [#GNDR-2620] - Customers counters update when deleting a user

## 3.5.4 - 2022-08-23

### Added

- [#GNDR-2835] - Update state field format sent to Hubspot
- [#GNDR-2566] - Hubspot sync update - Phone numbers must go without country code

## 3.5.3 - 2022-08-09

### Added

- [#GNDR-2252] - Supply management in Nova
- [#GNDR-2251] - Supply Category management in Nova
- [#GNDR-2554] - New QA members to user seeders
- [#GNDR-2431] - Create InApp, push and SMS notification for order assigned
- [#GNDR-2417] - Create pubnub message for order assigned

## 3.5.2 - 2022-08-04

### Added

- [#GNDR-2493] - Move source code from task GNDR-776 to branch rc-3.5.2
- [#GNDR-2407] - Store last message timestamp on pubnub_channels table
- [#GNDR-2207] - Supplier Cancellation Notification message updated
- [#GNDR-2225] - Remove required validation in eta field
- [#GNDR-2044] - Add completed orders to unauthenticated mobile web view

## 3.5.1 - 2022-08-02

### Fixed

- [#GNDR-2488] - Orders getting to Unpublished Suppliers

## 3.5.0 - 2022-07-28

### Added

- [#GNDR-2314] - Search by Model # filtering logic update
- [#GNDR-2202] - Export invoices change environment and datetime execution
- [#BD-89] - Invoices generation
- [#GNDR-2139] - Send notification on order approval instead of order completion
- [#GNDR-1928] - Change technical approach for automatic Live Quote message
- [#GNDR-1916] - Suppliers List sorting updated
- [#BL-53] - Log when order sent to another local supplier
- [#GNDR-1922] - Known replacements sorting update
- [#GNDR-1807] - SMS Notifications to Technicians text update
- [#BD-112] - Add configurable notifications
- [#GNDR-1859] - Switch Phone # pointing on "Call Branch" chat button
- [#GNDR-1636] - Search OEMs by model number
- [#GNDR-1653] - Add notes to resource
- [#GNDR-1367] - Nova page to set the URL value
- [#GNDR-1083] - Inbound - Working on it enable Edit to empty state
- [#GNDR-1245] - Added new delivery address field to Order
- [#GNDR-1609] - Add BID number to the cancel by technician pubnub channel message
- [#GNDR-1492] - Add user_id to part_detail_counter and create the IncrementPartViews action
- [#GNDR-1368] - Endpoint to get the URL value
- [#GNDR-1411] - Add user_id to oem_detail_counter and create the IncrementOemViews action
- [#GNDR-1412] - Log part searches
- [#GNDR-1410] - Send automatic message when an Order is declined by supplier
- [#GNDR-1015] - Send automatic message when a Quote is canceled by Technician
- [#GNDR-40] - Technical Debt - Replace oem.unit_type with foreign key

### Fixed

- [#GNDR-2285] - Number not correct on Account NavBar button
- [#GNDR-2213] - Invalid date when a suppliers edit a quote after ETA time
- [#GNDR-2211] - Error when Decline an order request
- [#GNDR-2177] - Api call fails when a tech approves an order without bid_number
- [#GNDR-2074] - Automatic message for live quote is not sent
- [#GNDR-1802] - Owner type filter on password reset
- [#GNDR-2042] - Mobile Live Quote message tap to approve doesn’t work
- [#GNDR-1789] - App search not working

## 3.4.5 - 2022-07-08

### Fixed

- [#GNDR-2062] - Chat channel fetching

## 3.4.4 - 2022-06-29

### Fixed

- [#GNDR-1876] - Technicians SMS Notification update "Availability" Format displayed

## 3.4.3 - 2022-06-28

### Added

- [#GNDR-1798] - Technicians SMS Notification - Add "Availability" completed by Supplier

### Fixed

- [#GNDR-1803] - Check sorting for known replacements

## 3.4.2 - 2022-06-23

### Fixed

- [#GNDR-1756] - Automatic price change when it ends with 0 cents
- [#GNDR-1752] - Mobile App Orders tab - All Orders linked to the same Pubnub channel

## 3.4.1 - 2022-06-23

### Added

- [#GNDR-1749] - Turn off some notifications

## 3.4.0 - 2022-06-22

### Added

- [#GNDR-1702] - Update link for Supplier email notification when Order is Rejected
- [#GNDR-1700] - Add links into SMS Supplier notifications
- [#BD-111] - Supplier App notifications
- [#GNDR-1453] - Added tip_id to parts table
- [#GNDR-1487] - Refactor model_description in base oem resources
- [#BD-110] - Email notifications
- [#BD-109] - Sms notifications
- [#GNDR-1133] - Add Supplier show endpoint
- [#GNDR-1139] - Change to "working on it" inbound functionality criteria
- [#GNDR-1311] - Oldest pending order attribute added to inbound endpoints
- [#GNDR-1131] - Create pubnub channels when a supplier is verified
- [#GNDR-1132] - Send auto reply message to user when new order is created
- [#GNDR-741] - Send automatic message to supplier when new order is created
- [#GNDR-1123] - Updated sorting by notes in know replacements
- [#GNDR-853] - Outbound - BE-Orders sorting update
- [#GNDR-1129] - Add Known replacements selection on 3 endpoints
- [#GNDR-850] - Update user company coordinates when updating company info in account
- [#GNDR-1130] - working_on_it_field to inbound user resource
- [#GNDR-1120] - Add images to supply endpoints
- [#GNDR-1119] - Add images to supply category endpoints
- [#GNDR-950] - Add oldest pending order created at date
- [#GNDR-1118] - Import SupplyCategory images script
- [#GNDR-1114] - Add support for media library image to SupplyCategory model
- [#GNDR-759] - Added preferred ordering
- [#GNDR-1098] - Adjust filter criteria in channel lists
- [#GNDR-1019] - Update chats tabs
- [#GNDR-767] - Update inbound list
- [#GNDR-1013] - Pubnub channel in inbound
- [#GNDR-766] - Create the channel record
- [#GNDR-757] - Update endpoint/request/resource
- [#GNDR-758] - Add Preferred supplier
- [#GNDR-756] - Add requested availability to orders table
- [#GNDR-823] - Endpoint all channels by tech
- [#GNDR-755] - Endpoint to store item_order / update resource
- [#GNDR-678] - Concatenate replacement notes and new part notes
- [#BL-31] - Supplier Company association
- [#BL-30] - Supplier Company
- [#GNDR-747] - Approve Unauthenticated order endpoint
- [#GNDR-754] - New table custom_item, new column custom_detail
- [#GNDR-762] - Set Pubnub SDK Setup
- [#GNDR-749] - Timezone column to suppliers table
- [#GNDR-291] - Create Internal Notification post endpoint
- [#GNDR-990] - Remove QA and UAT form part/supplies demo seeder
- [#GNDR-764] - Cancel pending or pending_approval order endpoint
- [#GNDR-762] - Add subcategory field to inbound and outbound endpoints
- [#GNDR-566] - Create an unauthenticated endpoint of order items list
- [#GNDR-830] - Small refactor
- [#GNDR-70] - Order Items endpoint
- [#GNDR-744] - Cancel approved or completed order endpoint
- [#GNDR-666] - Added filter to list of Orders
- [#GNDR-195] - Add Pubnub channel to resources of order
- [#GNDR-702] - Add address and phone to the resource of Order
- [#GNDR-201] - Send Technicians FCM token list to channel payload
- [#GNDR-540] - Create unauthenticated order endpoint
- [#GNDR-548] - Add filter to outbound order items
- [#GNDR-541] - Update cancel order endpoint
- [#CAN-3788] - User Order resource small improvements
- [#GNDR-250] - Known replacements Order criteria update
- [#GNDR-89] - Replace parts importer command for demo parts seeder
- [#CAN-3782] - Update Order resource in pickup/delivery endpoint
- [#GNDR-197] - Set Pubnub channels from Private to Public
- [#GNDR-88] - Make sure all not existing endpoints below live-api return json
- [#GNDR-123] - Add phone to supplier channel resource
- [#GNDR-122] - Add completing data to supplier channel resource
- [#GNDR-69] - Order details Endpoint
- [#GNDR-95] - Cancel order endpoint

### Changed

- [#GNDR-1581] - The url of invite email to bluon.com
- [#DATA-57] - Tonnage datatype in Oems table

### Fixed

- [#GNDR-1741] - Fix Suppliers main phone number for SMS
- [#GNDR-1674] - Fix Suppliers list sorting on the Order flow creation
- [#GNDR-1619] - Part list alphabetically sorted in part index
- [#GNDR-1678] - Include Prokeep Phone # on SMS notifications Feature
- [#GNDR-1698] - Chat channels not created when a Supplier is turned On the Network
- [#GNDR-1610] - Order link URL in order created notification email
- [#GNDR-1502] - Formatted the date on email based on supplier timezone
- [#GNDR-1503] - Fix sms encoding
- [#GNDR-1487] - The OEM of an order is not stored
- [#GNDR-1499] - Fixed order summary links in emails
- [#GNDR-1401] - Fixed update order item status to pending
- [#GNDR-1336] - Update coordinates when updating tech company zip code
- [#GNDR-1401] - Fix photo url in user resource
- [#GNDR-1337] - Fix is validating mandatory fields trying to save items on status "Not available" or "See below items"
- [#GNDR-1088] - Fix customer data is not updating correctly
- [#GNDR-1077] - Outbound Listing - Company details
- [#GNDR-1057] - Deny login and reset password to staff than no are owner
- [#GNDR-1018] - Duplicate users in Inbound user index
- [#GNDR-1025] - Fix Customers Dashboard - User's Name and Image not showing
- [#GNDR-1010] - Fix migration check primary key
- [#GNDR-1018] - Duplicate users in Inbound user index
- [#GNDR-852] - Outbound orders sort
- [#GNDR-706] - Pubnub channel naming convention changed
- [#GNDR-611] - Error calling Hubspot function setUserAccreditation

## 3.3.2 - 2022-06-16

### Fixed

- [#GNDR-1514] - Some manuals were not showed to the user

## 3.3.1 - 2022-06-03

### Fixed

- [#BL-50] - Change supplier subtitle from airtable id to Nova ID

## 3.3.0 - 2022-05-09

### Added

- [#GNDR-550] - Add subcategory field to part resource
- [#GNDR-537] - Other part description search equipment
- [#GNDR-37] - Customer tier label
- [#GNDR-108] - On Nova corrected the counter staff data display
- [#GNDR-117] - Endpoint to get list of chats
- [#GNDR-116] - Endpoint to Complete order
- [#GNDR-310] - Description for Other parts
- [#DATA-49] - Table replacement_sources
- [#GNDR-94] - Approve order endpoint
- [#BL-24] - Supplier Users endpoints and Supplier User fields
- [#GNDR-261] - Refactor send live and reopen responses
- [#GNDR-50] - Order fees endpoint
- [#GNDR-110] - Add pubnub_channel key to InProgressController existing resource
- [#DATA-40] - Column subcategory to parts tables
- [#GNDR-107] - Update hubspot company data on supplier creation in Nova
- [#GNDR-115] - Sorting criteria on in progress orders list
- [#GNDR-55] - Send for approval and reopen endpoints
- [#GNDR-56] - Order policies
- [#DATA-31] - Specifications columns to specific parts tables
- [#DATA-23] - VRF system type
- [#GNDR-60] - Bid number and availability support
- [#CAN-3400] - Add replacement on ItemOrder endpoint
- [#CAN-3405] - Order delivery delete endpoint
- [#CAN-3404] - Order delivery creation endpoint
- [#CAN-3283] - Import Common Items from Airtable
- [#CAN-3486] - Orders list
- [#CAN-3494] - Replace quotes endpoint with an outbound orders endpoint
- [#CAN-3584] - Modify inbound order detail endpoint
- [#CAN-3403] - Order delivery support
- [#CAN-3490] - Supplies endpoint
- [#CAN-3418] - Grouped replacements seed data
- [#CAN-3515] - Refactor supply categories
- [#CAN-3281] - Supply categories dummy data
- [#CAN-3278] - Common item endpoints
- [#CAN-2722] - Create order
- [#CAN-3399] - Support for item order replacements
- [#CAN-3461] - Filtering Oems list By Model
- [#CAN-3422] - Create verified suppliers existence endpoint
- [#CAN-3495] - Modify user orders list endpoint (inbound)
- [#CAN-3493] - Modify inbound order endpoint
- [#CAN-3492] - Support for order status
- [#CAN-2986] - DropDown list of user orders
- [#CAN-3488] - Centralize QA seeders
- [#CAN-3421] - BE Add specs grouping
- [#CAN-3333] - Create order item replacements list endpoint
- [#CAN-3367] - Compatible parts
- [#CAN-3368] - Part detail hit counter
- [#CAN-3366] - Part detail
- [#CAN-3365] - Part list filtered by number
- [#CAN-3326] - Support for model visualization count
- [#CAN-3274] - BE Support (geolocation and published scope modifications)
- [#CAN-2215] - Inbound messaging feature
- [#CAN-3437] - Create oem detail endpoint
- [#CAN-3435] - Filter search on brands list
- [#CAN-2988] - Create working on it endpoint
- [#CAN-3307] - Add tips in part resource
- [#CAN-2184] - Create tips table and tip relation in parts table
- [#CAN-3315] - Create order item endpoint for show info
- [#CAN-3282] - Common items DB structure
- [#CAN-2914] - Endpoint for invite to bluon
- [#CAN-3288] - Technician chats endpoint
- [#CAN-3338] - Create oems endpoint
- [#CAN-3056] - Create quotes items endpoint
- [#CAN-2641] - Integrate SOP with Hubspot for Suppliers tracking
- [#CAN-3055] - Create quotes endpoint
- [#CAN-3155] - Series endpoint by selected brand
- [#CAN-2184] - Create tips table and tip relation in parts table
- [#CAN-3077] - Create quotes db support
- [#CAN-2987] - Order detail endpoint
- [#CAN-3156] - Brand List V1 order alphabetically
- [#CAN-3021] - Supplier's list search zipCode not exclusive
- [#CAN-3199] - Order tables support
- [#CAN-3051] - Toggle in Nova to turn a Store On/Off the Network
- [#CAN-2817] - Suppliers endpoint
- [#BL-28] - Supplier Hours fields in Nova

### Changed

- [#DATA-46] - Belts column size
- [#DATA-50] - Column sizes in prod that were not in migrations
- [#DATA-42] - Motors column sizes
- [#DATA-41] - Specific parts column sizes
- [#CAN-3270] - Series count cast for Brands

### Fixed

- [#CAN-3638] - Fix no suppliers pop-up show conditions

## 3.2.5 - 2022-05-05

### Fixed

- [#GNDR-364] - HubSpot should map to the Nova ID instead of the HubSpot ID
-

## 3.2.4 - 2022-05-04

### Fixed

- [#GNDR-364] - HubSpot should map to the Nova ID instead of the HubSpot ID

## 3.2.3 - 2022-05-02

### Fixed

- [#GNDR-364] - HubSpot should map to the Nova ID instead of the HubSpot ID

## 3.2.2 - 2022-04-19

### Fixed

- [#GNDR-130] - CSRF error on app password reset

## 3.2.1 - 2022-04-14

### Fixed

- [#CAN-3629] - Hubspot updating old data

## 3.2.0 - 2022-03-04

### Added

- [#CAN-3609] - Image field to each part in parts lists
- [#CAN-3478] - Model type morph alias
- [#CAN-3437] - Indexes tables parts and oems
- [#CAN-2641] - Integrate SOP with Hubspot for Suppliers tracking
- [#CAN-3332] - Column uid to oem_part
- [#BD-37] - Edition of tags of type more in Nova
- [#CAN-3310] - Layout seeder for version 6.0.0
- [#CAN-3260] - By field Number ordering criteria on list of parts
- [#BD-3273] - Update Data Base to new Structure
- [#CAN-3208] - By field Type ordering criteria on list of parts
- [#CAN-3138] - Import minimal set of data for UAT
- [#CAN-3124] - Add second line information in Models list screen
- [#CAN-3137] - Add missing posts count field
- [#CAN-2143] - Parts list endpoint
- [#CAN-2892] - Oem db structure
- [#CAN-2959] - Model detail endpoint
- [#CAN-3092] - Add series count to brands
- [#CAN-2960] - Series endpoint
- [#CAN-2961] - Brands endpoint
- [#CAN-2664] - Manuals counts in OEM
- [#CAN-2183] - Part detail endpoint
- [#BD-85] - Phone fields to Nova user
- [#CAN-2664] - Manuals counts in OEM
- [#CAN-2958] - Models endpoint
- [#CAN-2169] - Parts db structure
- [#BD-76] - Unit Types

### Changed

- [#BD-84] - Nova user mailing address with company information
- [#BD-88] - Technicians use DO to store images instead of local filesystem

### Removed

- [#CAN-3475] - Daily product sync from schedule

### Fixed

- [#CAN-3352] - IOM manuals duplicated into misc manuals
- [#CAN-3162] - Oems displaying no matter the brand/series combination
- [#CAN-3165] - Users where unable to login on certain conditions

## 3.1.0 - 2022-02-21

### Added

- [#CAN-2889] - Improve 404 responses
- [#CAN-2889] - Suppliers morph alias
- [#CAN-2733] - User cannot request SMS code more than x times
- [#BD-86] - Airtable id to subtitles
- [#CAN-2825] - Relation morph maps to live api routes
- [#BL-26] - Fields take_rate to suppliers table and nova, Created limited-supplier endpoint
- [#BL-27] - Fields take_rate_until to suppliers table and nova
- [#CAN-2758] - Created before filter for post index endpoint
- [#BD-78] - New button "HubSpot Form" in nova
- [#CAN-2796] - Ability to add/edit supplier
- [#CAN-2775] - Base part's db structure
- [#BD-70] - Ability to search by city in nova/suppliers
- [#CAN-2769] - Tests example comments

### Changed

- [#CAN-2865] - Make DemoStaff seeder dont throw errors
- [#CAN-2860] - Move seeders to parts migration
- [#CAN-2829] - Sync demo staff and developers
- [#CAN-2653] - Supplier factory email to unique
- [#BL-22] - Staff email is changed on supplier email change
- [#DEV-297] - Pipeline tests db engine from sqlite to mySql 8
- [#CAN-2761] - Demo staff command into seeder
- [#BD-75] - Subtitle now shows full address in nova/suppliers

### Removed

- [#CAN-2896] - Phone as requirement for user verification

## 3.0.2 - 2022-02-02

### Added

- [#CAN-2737] - Stub suppliers seed
