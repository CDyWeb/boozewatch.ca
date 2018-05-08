<?
$this->parse(<<<LINES
#----------------------------------------

_date_tpl=Y-m-d
jq.datepicker.dateFormat=mm/dd/yy
monthNames=January,February,March,April,May,June,July,August,September,October,November,December
monthNamesShort=Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec
dayNames=Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday
dayNamesShort=Sun,Mon,Tue,Wed,Thu,Fri,Sat
dayNamesMin=Su,Mo,Tu,We,Th,Fr,Sa

l.en=English
l.en-GB=English (GB)
l.en-US=English (US)
l.en-CA=English (CA)
l.fr-CA=French (CA)

Yes=Yes
No=No
OK=OK
Close=Close
Cancel=Cancel
Save=Save
Apply=Apply
Add=Add
Edit=Edit
EditMany=Edit group
Delete=Delete
Delete.confirm=Confirm to delete: %firstparam
Delete.confirmMany=Confirm to delete ALL SELECTED records!
Unlink=Unlink from this Category
Unlink.confirm=Confirm to delete from this Category: %firstparam
Up=Up
Down=Down
Details=Details
Search=Search
search=search
Duplicate=Duplicate
with selected=with selected
Error=Error
Today=Today
Next=Next
Previous=Previous
Invite=Invite
Back=Back
back=back

Pagination=Display per page

Crud.Cannot delete=Cannot delete item %id, delete related items first.

Crud.deleted=Deleted from %firstparam: %secondparam
Crud.deleted.many=Items deleted
Crud.updated=Information updated for: %secondparam
Crud.updated.many=Items updated
Crud.added=Added to %firstparam: %secondparam

XLS Export=Export to excel

validate.required=This field is required.
validate.remote=Please fix this field.
validate.email=Please enter a valid email address.
validate.url=Please enter a valid URL.
validate.date=Please enter a valid date.
validate.number=Please enter a valid number.
validate.digits=Please enter only digits
validate.creditcard=Please enter a valid credit card number.
validate.equalTo=Please enter the same value again.
validate.accept=Please enter a value with a valid extension.
validate.maxlength=Please enter no more than {0} characters.
validate.minlength=Please enter at least {0} characters.
validate.rangelength=Please enter a value between {0} and {1} characters long.
validate.range=Please enter a value between {0} and {1}.
validate.max=Please enter a value less than or equal to {0}.
validate.min=Please enter a value greater than or equal to {0}.

Login=Login
Password=Password
E-mail address=E-mail address
Lost your password=Lost your password?
Password reset link sent=A "Password reset link" has been sent to your email address. Click that link to reset your password.
Enter your new password=Please enter your new password
Password too simple=Your new password must at least be 4 characters long and must contain at least one number
Your password has been changed=Your password has been changed

wrong password=wrong password
user not found=user not found

My account=My account
Language=Language
Support=Support
Analytics=Analytics
Logout=Logout
en_US=English
nl_NL=Dutch
l.en=English
l.nl=Dutch
l.fr=French
l.de=German
l.en_CA=English (CA)
l.fr_CA=French (CA)


page.welcome=<h1>Welcome!</h1><p>Welcome to the CCMS.</p>

Changes_saved=Changes saved
Nothing_changed=Nothing changed

address_construct=%firstparam<br />%secondparam<br />%fifthparam %thirdparam<br />%sixthparam<br />

No data=No data

searched_for=<center>Searched for '%1', number of hits: %2<br /><a href='%3'>clear</a></center>
CustomerList.searched_for=<center>Searched for '%1', customers found: %2<br /><a href='%3'>clear</a></center>

ck_editor.click_here=Click here to edit

newsletter.confirm.txt=Newsletter confirm email

NewsletterGroup.null=Subscribed Online
NewsletterGroup.Customers=Customers

No recipients selected=No contacts selected
Resend is not possible=Resend is not possible
Sending newsletter done=Sending newsletter done, %size messages have been sent.

NewsletterRecipient.add_one=Invite one Contact
NewsletterRecipient.add_many=Invite many Contacts
NewsletterRecipient.add_many.label=Email addresses seperated by space or enter
NewsletterRecipient.add_csv=CSV import
NewsletterRecipient.add_csv.label=CSV file to upload

newsletter.email.invite=Hello [name],\n\nYou have received this email because you are invited to receive future email messages from %domain.\n\nIf you don't want this, just ignore this email and the invitation will automatically be cancelled.\n\nHowever, if you do want to receive e-mail messages from %domain, you will need to confirm your invitation by clicking on this link.\n\n[link]\n\nBest Regards\n%domain

Tel1=Phone
Email=E-mail
Fax=Fax
E-mail=E-mail
Subject=Subject

Invoice=Invoice
Order_id=Invoice number
Order_date=Date
customer_details_header=Customer details
customer_details_tracking_header=Shipping
customer_details_adr2=Delivery address
invoice.footer=Thanks for your business.

Order.log.new=Order received
Order.log.in_progress=In progress
Order.log.payed=Paid
Order.log.sent=Products sent
Order.log.cancelled=Order cancelled
Order.log.closed=Order done

Order.Send status notification=Send status notification e-mail

page.newsletter-send.title=Send message: %firstparam
page.newsletter-send.edit=Edit this message
page.newsletter-send.send=Send this message
page.newsletter-send.preview=Preview
page.newsletter-send.to_head=Send this message to:
page.newsletter-send.from=Sender
page.newsletter-send.to_manual=Also send to (seperated by space or enter):
page.newsletter-send.btnSend=Send!
page.newsletter-send.sending=Busy sending your message: %firstparam

page.newsletter-track.title=Track message: %firstparam
page.newsletter-track.properties.th=Message Properties
page.newsletter-track.properties.Subject=Subject
page.newsletter-track.properties.Sent=Sent
page.newsletter-track.properties.Total Recipients=Total Recipients

page.newsletter-track.statistics.th=Email Statistics
page.newsletter-track.statistics.Bounces=Bounces
page.newsletter-track.statistics.Released=Released
page.newsletter-track.statistics.Unsubscribes=Unsubscribes
page.newsletter-track.statistics.Opens=Opens
page.newsletter-track.statistics.Clicks=Clicks
page.newsletter-track.statistics.Forwards=Forwards
page.newsletter-track.statistics.Comments=Comments
page.newsletter-track.statistics.Complaints=Complaints
page.newsletter-track.statistics.Neither=Neither

page.newsletter-track.click.th=Click Report
page.newsletter-track.click.th.Unique Clicks=Unique Clicks
page.newsletter-track.click.th.Total Clicks=Total Clicks

page.newsletter-track.details.clicks=Clicks
page.newsletter-track.details.opens=Opens
page.newsletter-track.details.unsubscribes=Unsubscribes
page.newsletter-track.details.bounces=Bounces
page.newsletter-track.details.Contact=Contact
page.newsletter-track.details.Date=Date

newsletter.compare.link.caption=Compare
page.newsletter-compare.title=Compare email message tracking results
page.newsletter-compare.th.Subject=Subject
page.newsletter-compare.th.Date=Date
page.newsletter-compare.th.Recipients=Recipients
page.newsletter-compare.th.Opens=Opens
page.newsletter-compare.th.Clicks=Clicks
page.newsletter-compare.th.Bounces=Bounces
page.newsletter-compare.th.Unsubscribes=Unsubscribes

page.settings.main=General
page.settings.main.home_page=Home page
page.settings.main.is_online=Website is online

page.settings.company=Organization details
page.settings.company.name=Name
page.settings.company.address1=Address line 1
page.settings.company.address2=Address line 2
page.settings.company.city=City
page.settings.company.state=State / Province
page.settings.company.zip=Postal code
page.settings.company.country=Country
page.settings.company.tel1=Phone
page.settings.company.email=Email
page.settings.company.website=Website
page.settings.company.bank=Bank account
page.settings.company.iban=IBAN
page.settings.company.bic=BIC
page.settings.company.tax=Tax number
page.settings.company.reg=Bussiness Registration
page.settings.company.tel2=Fax
page.settings.company.tel3=Other Phone

page.settings.google=Google
page.settings.google.googleVerify=Verify ownership metatag code 
page.settings.google.googleAnalytics=Analytics code (XX-9999999-9)

page.shop.payment=Payment options
page.shop.payment.main=General
page.shop.payment.main.method-select=Active payment methods

page.shop.payment.option.visit=Pay on visit
page.shop.payment.option.visit_in_advance=Money transfer, collect at store
page.shop.payment.option.in_advance=Money transfer
page.shop.payment.option.account=On account
page.shop.payment.option.rembours=Rembours
page.shop.payment.option.cc=Credit card
page.shop.payment.option.ideal=Ideal
page.shop.payment.option.paypal=Paypal

page.settings.webshop.shipping_free=Free shipping from $

page.shop.stock.edit_all=Edit all

#----------------------------------------
LINES
,"system_txt");
//end








