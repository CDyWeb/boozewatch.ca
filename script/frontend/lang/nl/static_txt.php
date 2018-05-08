<?
$this->parse(<<<LINES
#----------------------------------------

yes=ja
no=nee

Yes=Ja
No=Nee

back=terug
Back=Terug

address_construct=%firstparam<br />%secondparam<br />%fifthparam %thirdparam<br />%sixthparam<br />
_date_tpl=d-m-Y

email.view-online=Onleesbaar? <a href="#newsletter-view">Klik hier</a> om online te bekijken.
email.unsubscribe-link=<a href="#newsletter-unsubscribe">Uitschrijven? Klik hier</a>. Bezoek <a href="#base-url">www.%domain</a> voor meer informatie.

Captcha mismatch=De captcha code is niet juist overgenomen. Probeer het nog eens.

plugin.customer.register_subject=Klant logingegevens

newsletter.email.subject=Aanmelden nieuwsbrief

news.read_more=Lees meer...
news.back=Terug

events.read_more=Lees meer...
events.back=Terug

newsletter.confirm.ok=Uw nieuwbrief inschrijving is geactiveerd.
newsletter.confirm.not-found=Uw inschrijving is niet gevonden.
newsletter.err.invalid_email=Dit is geen geldig email adres.
newsletter.err.empty_name=Voer uw naam in svp.
newsletter.ok.subscribe=We hebben uw inschrijving ontvangen. Activeer via email svp.
newsletter.ok.unsubscribe=U bent uitgeschreven.
newsletter.err.not-subscribed=U bent niet ingeschreven.

newsletter.email.subject=Nieuwsbrief inschrijving
newsletter.email.activate=

mailform_done_msg=<p>Uw bericht is verstuurd.<br />U ontvangt zo spoedig mogelijk een reactie.</p>



#----------------------------------------
LINES
);