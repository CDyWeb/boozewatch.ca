<?
$this->parse(<<<LINES
#----------------------------------------

Tree=Website structure
Tree.name=Name
Tree.active=Visible
Tree.text=Description
Tree.parent_id=Part of
Tree.class=Holds
Tree.class.Cat=Sub categories
Tree.class.Product=Products

Tree.MenuTop=Top menu
Tree.MenuLeft=Left menu
Tree.MenuRight=Right menu
Tree.MenuBottom=Bottom menu

Tree.Newsletter=Email Messaging
Tree.Language=Language

Tree.Cat=Collection
Tree.Shop=Shop
Tree.Customers=Customers
Tree.Shipping=Shipping
Tree.Payment=Payment
Tree.ProductStock=Product stock
Tree.img=Image

User=User
User._title=Users
User._name=Name
User.__NAME=Name
User.first_name=First name
User.last_name=Last name
User.email=E-mail
User.password=Password
User.user_type=Type
User.user_type.user=User
User.user_type.editor=Editor
User.user_type.super=Admin

Page=Page
Page._title=Pages
Page.indexable=Robot indexable
Page.name=Name
Page.text=Contents
Page.active=Visible
Page.attributes_link=Link
Page.attributes_plugin=Plugin
Page.tree_id=Menu
Page.parent_id=Subpage of
Page.meta_title=Title
Page.meta_keywords=Keywords
Page.meta_description=Description
Page.uri=Fixed URI
Page.page_type=Type
Page.page_type.text=Text
Page.page_type.link=Link
Page.page_type.plugin=Plugin
Page.page_type.menu=Menu

Settings=Settings
Settings._title=Settings
Settings.key=Name
Settings.str=Value
Settings.txt=Value

Tax=Tax
Tree.Tax=Tax
Tax._title=Tax rates
Tax.name=Name
Tax.percent=Per cent

Brand=Brand
Tree.Brand=Brands
Brand._title=Brands
Brand.name=Name
Brand.link=Website
Brand.img=Image
Brand.description=Description

ShippingRate=Shipping rate
ShippingRate._title=Shipping rates
ShippingRate.name=Name
ShippingRate.rate=Rate
ShippingRate.tax=Tax

Ship_Country=Country
Ship_Country._title=Countries
Ship_Country.name=Name
Ship_Country.active=Active
Ship_Country.rate=Additional shipping fee
Ship_Country.with_tax=Tax
Ship_Country.free_shipping_offset=Free shipping offset

Cat=Collection
Cat._title=Collection

Product=Product
Product._title=Products
Product.active=Visible
Product.name=Name
Product.sku=SKU
Product.brand=Brand
Product.price=Price
Product.tax=Tax rate
Product.shippingrate=Shipping rate
Product.description=Description
Product.keywords=Keywords
Product.img=Image
Product.tree_id=Category
Product.multi_cat=Also in
Product.discount_absolute=Discount (abs)
Product.discount_percent=Discount (%)
Product.discount_start=Discount start
Product.discount_end=Discount until
Product.units=Units
Product.units.piece=Piece
Product.quantity=Quantity per unit
Product.stock=Stock
Product.no_stock_action=When not in stock
Product.no_stock_action.ignore=Ignore
Product.no_stock_action.notify=Notify
Product.no_stock_action.hide=Hide
Product.deliveryperiod=Delivery period (days)
Product.subimg1=Sub image 1
Product.subimg2=Sub image 2
Product.subimg3=Sub image 3
Product.is_new=Show as 'New' product
Product.is_hot=Product Promotion
Product.is_home=Show on home
Product.option1=Options 1
Product.option2=Options 2
Product.option3=Options 3
Product.option3=Options 4
Product._discount=Discount
Product._list.discount_start=Start
Product._list.discount_end=End

ProductGallery._title=Gallery
ProductGallery.pubdate=Pubdate
ProductGallery.title=Title
ProductGallery.link=Link
ProductGallery.enclosure=Image
ProductGallery.description=Description
ProductGallery.author=Author

ProductNotify=Stock notify
ProductNotify._title=Stock notify
ProductNotify.product=Item
ProductNotify.email=E-mail adres

Customer=Customer
Customer._title=Customers
Customer.__NAME=Name
Customer.__REWARD=Reward points
Customer.credit=Credit
Customer.customer_id=Customer number
Customer.company=Company
Customer.title=Title
Customer.title.mr=Mr.
Customer.title.mrs=Mrs.
Customer.title.ms=Ms.
Customer.title.miss=Miss.
Customer.title.dr=Dr.
Customer.title.prof=Prof.
Customer.first_name=First name
Customer.last_name=Last name
Customer.dob=DOB
Customer.newsletter=Newsletter
Customer.email=E-mail address
Customer.password=Password
Customer.tel1=Phone
Customer.tel2=Cell
Customer.tel3=Fax
Customer.adr1_address1=Address
Customer.adr1_address2=Address 2
Customer.adr1_city=City
Customer.adr1_state=Region
Customer.adr1_zip=Postcode
Customer.adr1_country=Country
Customer.adr2_name=Delivery name
Customer.adr2_address1=Address
Customer.adr2_address2=Address 2
Customer.adr2_city=City
Customer.adr2_state=Region
Customer.adr2_zip=Postcode
Customer.adr2_country=Country

Order=Order
Tree.Orders=Orders
Order._title=Orders
Order.pon=Purchase Order number
Order.printed=Printed
Order.order_id=Order number
Order.customer=Customer
Order.customer_name=Customer
Order.uid=uid
Order.currency=Currency
Order.currency_factor=Exchange rate
Order.payment=Payment method
Order.payment.visit=On visit
Order.payment.in_advance=Wire transfer
Order.payment.visit_in_advance=Wire transfer, pick up
Order.payment.rembours=Rembours
Order.payment.ideal=iDEAL
Order.payment.mrcash=MrCash
Order.payment.directebanking=DIRECTebanking
Order.payment.paypal=PayPal
Order.payment.account=Customer account
Order.payment.cc=Credit Card
Order.date_insert=Order date
Order.date_update=Last change date
Order.status=Status
Order.status.new=Wait for payment
Order.status.in_process=In progress
Order.status.backorder=Back order
Order.status.payed=Paid
Order.status.sent=Sent
Order.status.closed=Closed
Order.status.cancelled=Cancelled
Order.am_subtotal=Sub total
Order.am_processing=Handling costs
Order.am_transport=Shipping
Order.am_tax=Tax
Order.am_total=Grand total
Order.tracking=Tracking info
Order.language=Language

CustomerOrder=Order
CustomerOrder._title=Orders

Newsletter=Email Message
Newsletter._title=Email Messages
Newsletter.name=Subject
Newsletter.dt_sent=Sent on
Newsletter.template=Template
Newsletter.language=Language
Newsletter.text_top=General text top
Newsletter.text_bottom=General text bottom

Newsletter._item_=Item %firstparam

NewsletterItem.type=Type
NewsletterItem.type.text=Text
NewsletterItem.type.product=Product
NewsletterItem.type.news=News item
NewsletterItem.fk=Item
NewsletterItem.title=Title
NewsletterItem.caption=Caption
NewsletterItem.text=Text
NewsletterItem.image=Image

Newsletter._send=Send
Newsletter._track=Track

NewsletterGroup=Contact List
NewsletterGroup._title=Contact Lists
NewsletterGroup._null=Subscribed Online
NewsletterGroup._count=Number of contacts
NewsletterGroup.name=List Name
NewsletterGroup.auto_activate=Confirm automatically
NewsletterGroup._Add=Create new Contact List

NewsletterRecipient=Contact
NewsletterRecipient._title=Contacts
NewsletterRecipient.newsletter_group=Contact List
NewsletterRecipient.name=Name
NewsletterRecipient.email=E-mail
NewsletterRecipient.residence=Residence
NewsletterRecipient.language=Language
NewsletterRecipient.status=Status
NewsletterRecipient.status.new=New
NewsletterRecipient.status.pending=Pending
NewsletterRecipient.status.confirmed=Confirmed
NewsletterRecipient.status.bounce=Bounce
NewsletterRecipient._invite_txt=Invitation text
NewsletterRecipient._Add=Add Contacts to this List

AliasDomain=Alias Domain
AliasDomain._title=Alias Domains
AliasDomain.active=Active
AliasDomain.domain=Domain name
AliasDomain.meta_title=Title
AliasDomain.meta_keywords=Keywords
AliasDomain.meta_description=Description
AliasDomain.text=Text
AliasDomain.analytics=Custom analytics code

SizeGroup=Size group
SizeGroup._title=Size groups
SizeGroup.name=Size group

Size=Size
Size._title=Sizes
Size._add_many=Add multiple sizes
Size.sizegroup=Size group
Size.name=Size

ProductSize=Available Size
ProductSize._title=Available Sizes
ProductSize.product=Product
ProductSize.size=Size
ProductSize.active=Available
ProductSize.sku=SKU
ProductSize.price=Price
ProductSize.stock=Stock
ProductSize.img=Image

Voucher=Promotion Code
Voucher._title=Promotion Codes
Voucher.barcode=Code
Voucher.value=Value
Voucher.date_insert=Date inserted
Voucher.date_update=Date updated
Voucher.uid=UID
Voucher.order=Order
Voucher.percent=Percent
Voucher.free_shipping=Free shipping
Voucher.free_shipping_above=Free shipping above
Voucher.active=Active
Voucher.date_exp=Expiry date

Sidebar=Sidebar
Sidebar._title=Sidebar
Sidebar.active=Visible
Sidebar.name=Name
Sidebar.sidebar_type=Type
Sidebar.sidebar_type.text=Text
Sidebar.text=Content

Plugin_News_PageNews._title=News
PageNews._title=News
PageNews.pubdate=Date
PageNews.title=Title
PageNews.link=Link
PageNews.enclosure=Image
PageNews.description=Text
PageNews.is_home=Show on homepage
PageNews.is_hot=Highlight
PageNews.product_id=Product
PageNews.page_id=Page

PageGallery._title=Gallery
PageGallery.pubdate=Date
PageGallery.title=Title
PageGallery.link=Link
PageGallery.enclosure=Image
PageGallery.description=Text
PageGallery.is_home=Show on homepage
PageGallery.is_hot=Highlight
PageGallery.product_id=Product

Slider._title=Slider Images
Slider.title=Title
Slider.enclosure=Image
Slider.visible=Visible on page

#----------------------------------------
LINES
);
//end