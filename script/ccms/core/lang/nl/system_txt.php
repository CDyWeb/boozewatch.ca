<?
$this->parse(<<<LINES
#----------------------------------------

_date_tpl=d-m-Y
jq.datepicker.dateFormat=dd-mm-yy
monthNames=januari,februari,maart,april,mei,juni,juli,augustus,september,oktober,november,december
monthNamesShort=jan,feb,maa,apr,mei,jun,jul,aug,sep,okt,nov,dec
dayNames=zondag,maandag,dinsdag,woensdag,donderdag,vrijdag,zaterdag
dayNamesShort=zon,maa,din,woe,don,vri,zat
dayNamesMin=zo,ma,di,wo,do,vr,za

Yes=Ja
No=Nee
OK=OK
Close=Sluiten
Cancel=Annuleren
Save=Opslaan
Apply=Toepassen
Add=Toevoegen
Edit=Bewerken
EditMany=Groep bewerken
Delete=Verwijderen
Delete.confirm=Bevestig het verwijderen van: %firstparam
Delete.confirmMany=Bevestig het verwijderen van ALLE GESELECTEERDE REGELS!
Unlink=Verwijderen uit deze categorie
Unlink.confirm=Bevestig het verwijden uit deze categorie: %firstparam
Up=Omhoog
Down=Omlaag
Details=Details
Search=Zoeken
search=zoek
Duplicate=Dupliceren
with selected=geselecteerde regels
Error=Fout
Today=Vandaag
Next=Volgende
Previous=Vorige
Invite=Uitnodigen
Back=Terug
back=terug

Pagination=Weergave per pagina

Crud.Cannot delete=Item %id kan niet worden verwijderd, verwijder eerst de gekoppelde items.

Crud.deleted=Verwijderd van %firstparam: %secondparam
Crud.deleted.many=Items verwijderd
Crud.updated=Gegevens gewijzigd voor: %secondparam
Crud.updated.many=Gegevens gewijzigd
Crud.added=Toegevoegd aan %firstparam: %secondparam

XLS Export=Exporteren naar excel

validate.required=Dit veld is verplicht.
validate.remote=Please fix this field.
validate.email=Voer svp een geldig email adres in.
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

Login=Inloggen
Password=Wachtwoord
E-mail address=E-mail adres
Lost your password=Wachtwoord vergeten?
Password reset link sent=Er is een "wachtwoord reset link" naar uw email adres gestuurd. Klik op die link om uw wachtwoord te resetten.
Enter your new password=Voer uw nieuwe wachtwoord in
Password too simple=Het nieuwe wachtwoord moet minimaal 4 karakters lang zijn, waarvan minstens 1 cijfer
Your password has been changed=Uw wachtwoord is aangepast

wrong password=onjuist wachtwoord
user not found=gebruiker niet bekend

My account=Mijn account
Language=Taal
Support=Support
Analytics=Analytics
Logout=Logout
en_US=Engels
nl_NL=Nederlands
l.en=Engels
l.nl=Nederlands
l.de=Duits
l.fr=Frans
l.en_CA=Engels (CA)
l.fr_CA=Frans (CA)

page.welcome=<h1>Welkom!</h1><p>Welkom in het CCMS.</p>

Changes_saved=De wijzigingen zijn opgeslagen
Nothing_changed=Er is niets gewijzigd

address_construct=%firstparam<br />%secondparam<br />%fifthparam %thirdparam<br />%sixthparam<br />

No data=Geen gegevens

searched_for=<center>Gezocht op '%1', aantal gevonden overeenkomsten: %2<br /><a href='%3'>wissen</a></center>
CustomerList.searched_for=<center>Gezocht op '%1', aantal gevonden klanten: %2<br /><a href='%3'>wissen</a></center>

Tel1=Telefoon
Email=Email
Fax=Fax
E-mail=E-mail
Subject=Onderwerp

ck_editor.click_here=Klik hier om te wijzigen

newsletter.confirm.txt=Nieuwsbrief bevestigen

NewsletterGroup.null=-Algemeen-
NewsletterGroup.Customers=Klanten

No recipients selected=Geen ontvangers geselecteerd
Resend is not possible=Opnieuw verzenden is niet mogelijk
Sending newsletter done=Versturen gereed, %size email berichten zijn verstuurd.

NewsletterRecipient.add_one=Eén ontvanger uitnodigen
NewsletterRecipient.add_many=Lijst uitnodigen
NewsletterRecipient.add_many.label=Email adressen, gescheiden door komma en/of nieuwe regel
NewsletterRecipient.add_csv=CSV import
NewsletterRecipient.add_csv.label=CSV bestand


newsletter.email.invite=Beste [naam],\n\n\nU ontvangt deze email omdat u wordt uitgenodigd om voortaan de email nieuwsbrief van %domain te ontvangen.\n\nIndien u dit niet wilt, negeer dan dit bericht en de uitnodiging wordt automatisch geannuleerd.\n\nEchter, als u e-mailings van %domain wilt ontvangen, zal u uw uitnodiging moeten bevestigen door op onderstaande link te klikken.\n\n[link]\n\nMet vriendelijke groeten,\n%domain

Invoice=Factuur
Order_id=Factuurnummer
Order_date=Datum
customer_details_header=Klantgegevens
customer_details_tracking_header=Verzendinformatie
customer_details_adr2=Leveradres
invoice.footer=Bedankt voor uw klandizie.

Order.log.new=Bestelling ontvangen
Order.log.in_progress=In behandeling
Order.log.payed=Betaald
Order.log.sent=Producten zijn verstuurd
Order.log.cancelled=Order geannuleerd
Order.log.closed=Afgehandeld

Order.Send status notification=Verstuur status update e-mail

page.newsletter-send.title=Nieuwsbrief versturen: %firstparam
page.newsletter-send.edit=Deze nieuwsbrief bewerken
page.newsletter-send.send=Deze nieuwsbrief versturen
page.newsletter-send.preview=Preview
page.newsletter-send.to_head=Verstuur deze nieuwsbrief aan:
page.newsletter-send.from=Afzender
page.newsletter-send.to_manual=Verstuur ook aan deze adressen (gescheiden door spatie en/of nieuwe regel):
page.newsletter-send.btnSend=Verstuur!
page.newsletter-send.sending=Bezig met versturen: %firstparam

page.newsletter-track.title=Track bericht: %firstparam
page.newsletter-track.properties.th=Bericht eigenschappen
page.newsletter-track.properties.Subject=Onderwerp
page.newsletter-track.properties.Sent=Verzonden
page.newsletter-track.properties.Total Recipients=Aantal ontvangers

page.newsletter-track.statistics.th=Email statistieken
page.newsletter-track.statistics.Bounces=Bounces
page.newsletter-track.statistics.Released=Verzonden
page.newsletter-track.statistics.Unsubscribes=Uitgeschreven
page.newsletter-track.statistics.Opens=Geopend
page.newsletter-track.statistics.Clicks=Clicks
page.newsletter-track.statistics.Forwards=Forwards
page.newsletter-track.statistics.Comments=Comments
page.newsletter-track.statistics.Complaints=Complaints
page.newsletter-track.statistics.Neither=Niets gemeten

page.newsletter-track.click.th=Click Report
page.newsletter-track.click.th.Unique Clicks=Unieke Clicks
page.newsletter-track.click.th.Total Clicks=Totaal Clicks

page.newsletter-track.details.clicks=Clicks
page.newsletter-track.details.opens=Geopend
page.newsletter-track.details.unsubscribes=Uitgeschreven
page.newsletter-track.details.bounces=Bounces
page.newsletter-track.details.Contact=Contact
page.newsletter-track.details.Date=Datum

newsletter.compare.link.caption=Vergelijk
page.newsletter-compare.title=Vergelijk nieuwsbrief resultaten
page.newsletter-compare.th.Subject=Onderwerp
page.newsletter-compare.th.Date=Datum
page.newsletter-compare.th.Recipients=Ontvangers
page.newsletter-compare.th.Opens=Opens
page.newsletter-compare.th.Clicks=Clicks
page.newsletter-compare.th.Bounces=Bounces
page.newsletter-compare.th.Unsubscribes=Unsubscribes

page.settings.main=Algemeen
page.settings.main.home_page=Home page
page.settings.main.is_online=Website is online

page.settings.company=Bedrijfsgegevens
page.settings.company.name=Naam
page.settings.company.address1=Adres
page.settings.company.address2=Adres toevoeging
page.settings.company.city=Plaats
page.settings.company.state=Regio
page.settings.company.zip=Postcode
page.settings.company.country=Land
page.settings.company.tel1=Telefoon
page.settings.company.email=Email
page.settings.company.website=Website
page.settings.company.bank=Bank rekening
page.settings.company.iban=IBAN
page.settings.company.bic=BIC
page.settings.company.tax=BTW nr.
page.settings.company.reg=KVK
page.settings.company.tel2=Fax
page.settings.company.tel3=Alt telefoon

page.settings.google=Google
page.settings.google.googleVerify=Verify ownership metatag code 
page.settings.google.googleAnalytics=Analytics code (XX-9999999-9)

page.shop.payment=Betaalopties
page.shop.payment.main=Algemeen
page.shop.payment.main.method-select=Actieve betaalmethodes

page.shop.payment.option.visit=Aan de kassa
page.shop.payment.option.visit_in_advance=Vooraf overboeken, afhalen
page.shop.payment.option.in_advance=Vooraf overboeken, versturen
page.shop.payment.option.account=Op rekening
page.shop.payment.option.rembours=Rembours
page.shop.payment.option.cc=Credit card
page.shop.payment.option.ideal=Ideal
page.shop.payment.option.paypal=Paypal

page.settings.webshop.shipping_free=Gratis verzenden vanaf &euro;

page.shop.stock.edit_all=Alles bewerken

#----------------------------------------
LINES
,"system_txt");
//end








