<?
$this->parse(<<<LINES
#----------------------------------------

yes=yes
no=no

Yes=Yes
No=No

back=back
Back=Back

address_construct=%firstparam<br />%secondparam<br />%fifthparam %thirdparam<br />%sixthparam<br />
_date_tpl=m/d/Y

email.view-online=Not readable? <a href="#newsletter-view">Click here</a> to view online.
email.unsubscribe-link=Unsubscribe from this newsletter? <a href="#newsletter-unsubscribe">Click here</a>. Please visit <a href="#base-url">www.%domain</a> for more information.

Captcha mismatch=The captcha security code did not match. Please try again.

plugin.customer.register_subject=Your login details

newsletter.email.subject=Newsletter subscribe

news.read_more=Read more...
news.back=Back

events.read_more=Read more...
events.back=Back

newsletter.confirm.ok=You have successfully activated your newsletter subscription.
newsletter.confirm.not-found=Your subscription request was not found. Please subscribe again.
newsletter.err.invalid_email=This is not a valid email address.
newsletter.err.empty_name=Enter your name please.
newsletter.ok.subscribe=We have successfully received your newsletter subscription request. Please confirm your subscription by clicking on the activation link that we have just sent you by e-mail.
newsletter.ok.unsubscribe=You have been unsubscribed successfully.
newsletter.err.not-subscribed=You are not subscribed.

newsletter.email.subject=Newsletter subscribe
newsletter.email.activate=_

mailform_done_msg=<p>Your form has been submitted.<br />You will be contacted shortly.</p>

#----------------------------------------
LINES
);