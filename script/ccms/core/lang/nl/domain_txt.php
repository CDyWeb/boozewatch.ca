<?
$this->parse(<<<LINES
#----------------------------------------

Tree=Website structuur
Tree.name=Naam
Tree.active=Zichtbaar
Tree.text=Omschrijving
Tree.parent_id=Onderdeel van
Tree.class=Bevat
Tree.class.Cat=Subcategorieën
Tree.class.Product=Producten

Tree.MenuTop=Menu bovenzijde
Tree.MenuLeft=Menu links
Tree.MenuRight=Menu rechts
Tree.MenuBottom=Menu onderzijde

Tree.Newsletter=Email Nieuwsbrieven
Tree.Language=Taal

Tree.Cat=Collectie
Tree.Shop=Instellingen
Tree.Customers=Klanten
Tree.Shipping=Verzendkosten
Tree.Payment=Betaalopties
Tree.ProductStock=Voorraadlijst
Tree.img=Afbeelding

User=Gebruiker
User._title=Gebruikers
User._name=Naam
User.__NAME=Naam
User.first_name=Voornaam
User.last_name=Achternaam
User.email=E-mail
User.password=Wachtwoord
User.user_type=Type
User.user_type.user=Gebruiker
User.user_type.editor=Editor
User.user_type.super=Beheerder

Page=Pagina
Page._title=Pagina's
Page.indexable=Indexeerbaar
Page.name=Naam
Page.text=Inhoud
Page.active=Zichtbaar
Page.attributes_link=Link
Page.attributes_plugin=Plugin
Page.tree_id=Menu
Page.parent_id=Onderdeel van
Page.meta_title=Titel
Page.meta_keywords=Trefwoorden
Page.meta_description=Omschrijving
Page.uri=Alternatief adres
Page.page_type=Type
Page.page_type.text=Tekst
Page.page_type.link=Link
Page.page_type.plugin=Plugin
Page.page_type.menu=Menu

Settings=Instellingen
Settings._title=Instellingen
Settings.key=Naam
Settings.str=Waarde
Settings.txt=Waarde

Tax=BTW
Tree.Tax=BTW
Tax._title=BTW tarieven
Tax.name=Naam
Tax.percent=Procent

Brand=Merk
Tree.Brand=Merken
Brand._title=Merken
Brand.name=Naam
Brand.link=Website
Brand.img=Afbeelding
Brand.description=Omschrijving

ShippingRate=Verzendtarief
ShippingRate._title=Verzendtarieven
ShippingRate.name=Naam
ShippingRate.rate=Tarief
ShippingRate.tax=BTW

Ship_Country=Land
Ship_Country._title=Landen
Ship_Country.name=Naam
Ship_Country.active=Actief
Ship_Country.rate=Toeslag
Ship_Country.with_tax=BTW
Ship_Country.free_shipping_offset=Gratis verzenden vanaf

Cat=Collectie
Cat._title=Collectie

Product=Product
Product._title=Producten
Product.name=Naam
Product.active=Zichtbaar
Product.sku=Artikelnr
Product.brand=Merk
Product.price=Prijs
Product.tax=BTW
Product.shippingrate=Verzendtarief
Product.description=Omschrijving
Product.keywords=Trefwoorden
Product.img=Afbeelding
Product.tree_id=Categorie
Product.multi_cat=Toon ook onder
Product.discount_absolute=Korting (absoluut)
Product.discount_percent=Korting (procent)
Product.discount_start=Aanbieding vanaf
Product.discount_end=Aanbieding tot
Product.units=Eenheid
Product.units.piece=Stuks
Product.quantity=Aantal in verpakking
Product.stock=Voorraad aantal
Product.no_stock_action=Indien niet op voorraad
Product.no_stock_action.ignore=Artikel blijft bestelbaar
Product.no_stock_action.notify=Inschrijven op notificatie e-mail
Product.no_stock_action.hide=Artikel verbergen
Product.deliveryperiod=Levertijd (dagen)
Product.subimg1=Sub afbeelding 1
Product.subimg2=Sub afbeelding 2
Product.subimg3=Sub afbeelding 3
Product.is_new=Tonen onder 'Nieuw'
Product.is_hot=Tonen onder 'Acties'
Product.is_home=Tonen op home
Product.option1=Opties 1
Product.option2=Opties 2
Product.option3=Opties 3
Product.option3=Opties 4
Product._discount=Korting
Product._list.discount_start=Vanaf
Product._list.discount_end=Tot

ProductGallery._title=Gallery
ProductGallery.pubdate=Datum
ProductGallery.title=Titel
ProductGallery.link=Link
ProductGallery.enclosure=Afbeelding
ProductGallery.description=Tekst
ProductGallery.author=Auteur

ProductNotify=Voorraad notificatie
ProductNotify._title=Voorraad notificaties
ProductNotify.product=Artikel
ProductNotify.email=E-mail adres

Customer=Klant
Customer._title=Klanten
Customer.__NAME=Naam
Customer.__REWARD=Spaarpunten
Customer.credit=Ruiltegoed
Customer.customer_id=Klantnummer
Customer.company=Bedrijf
Customer.title=Aanhef
Customer.title.mr=Dhr.
Customer.title.mrs=Mevr.
Customer.title.ms=Mej.
Customer.title.miss=Mevr.
Customer.title.prof=Prof.
Customer.title.dr=Dr.
Customer.first_name=Voornaam
Customer.last_name=Achternaam
Customer.dob=Geboortedatum
Customer.newsletter=Nieuwsbrief
Customer.email=E-mail adres
Customer.password=Wachtwoord
Customer.tel1=Telefoon
Customer.tel2=Mobiel
Customer.tel3=Fax
Customer.adr1_address1=Adres
Customer.adr1_address2=Toevoeging
Customer.adr1_city=Plaats
Customer.adr1_state=Provincie
Customer.adr1_zip=Postcode
Customer.adr1_country=Land
Customer.adr2_name=Afleveren op naam
Customer.adr2_address1=Adres
Customer.adr2_address2=Toevoeging
Customer.adr2_city=Plaats
Customer.adr2_state=Provincie
Customer.adr2_zip=Postcode
Customer.adr2_country=Land

Order=Order
Tree.Orders=Orders
Order._title=Orders
Order.pon=Inkoopnummer
Order.printed=Afgedrukt
Order.order_id=Ordernummer
Order.customer=Klant
Order.customer_name=Klant
Order.uid=uid
Order.currency=Valuta
Order.currency_factor=Wisselkoers
Order.payment=Betaalwijze
Order.payment.visit=Kassa
Order.payment.in_advance=Overschrijving
Order.payment.visit_in_advance=Overschrijving, zelf ophalen
Order.payment.rembours=Rembours
Order.payment.ideal=iDEAL
Order.payment.mrcash=MrCash
Order.payment.directebanking=DIRECTebanking
Order.payment.paypal=PayPal
Order.payment.account=Op rekening
Order.payment.cc=Credit Card
Order.date_insert=Datum ingevoerd
Order.date_update=Datum gewijzigd
Order.status=Status
Order.status.new=Wacht op betaling
Order.status.in_process=In behandeling
Order.status.backorder=Back order
Order.status.payed=Betaald
Order.status.sent=Verstuurd
Order.status.closed=Afgehandeld
Order.status.cancelled=Geannuleerd
Order.am_subtotal=Subtotaal
Order.am_processing=Administratiekosten
Order.am_transport=Verzendkosten
Order.am_tax=BTW
Order.am_total=Totaalbedrag
Order.tracking=Tracking info
Order.language=Taal

CustomerOrder=Order
CustomerOrder._title=Orders

Newsletter=Nieuwsbrief
Newsletter._title=Nieuwsbrieven
Newsletter.name=Onderwerp

Newsletter.name=Onderwerp
Newsletter.dt_sent=Verzonden op
Newsletter.template=Sjabloon
Newsletter.language=Taal
Newsletter.text_top=Algemene tekst boven
Newsletter.text_bottom=Algemene tekst onder
Newsletter._item_=Item %firstparam

NewsletterItem.type=Soort
NewsletterItem.type.text=Tekst
NewsletterItem.type.product=Product
NewsletterItem.type.news=Nieuwsbericht
NewsletterItem.fk=Item
NewsletterItem.title=Titel
NewsletterItem.caption=Onderschrift
NewsletterItem.text=Tekst
NewsletterItem.image=Afbeelding

Newsletter._send=Versturen
Newsletter._track=Track

NewsletterGroup=Nieuwsbrief adreslijst
NewsletterGroup._title=Nieuwsbrief adreslijsten
NewsletterGroup._null= - Algemeen -
NewsletterGroup._count=Aantal ontvangers
NewsletterGroup.name=Naam
NewsletterGroup.auto_activate=Automatisch bevestigen
NewsletterGroup._Add=Nieuwe adreslijst maken

NewsletterRecipient=Nieuwsbrief ontvanger
NewsletterRecipient._title=Nieuwsbrief ontvangers
NewsletterRecipient.newsletter_group=Adreslijst
NewsletterRecipient.name=Naam
NewsletterRecipient.email=E-mail
NewsletterRecipient.residence=Woonplaats
NewsletterRecipient.language=Taal
NewsletterRecipient.status=Status
NewsletterRecipient.status.new=Nieuw
NewsletterRecipient.status.pending=Wacht op bevestiging
NewsletterRecipient.status.confirmed=Bevestigd
NewsletterRecipient.status.bounce=Bounce
NewsletterRecipient._invite_txt=Uitnodiging
NewsletterRecipient._Add=Adressen toevoegen aan deze lijst

AliasDomain=Alias domein
AliasDomain._title=Alias domeinen
AliasDomain.active=Actief
AliasDomain.domain=Domeinnaam
AliasDomain.meta_title=Titel
AliasDomain.meta_keywords=Trefwoorden
AliasDomain.meta_description=Omschrijving
AliasDomain.text=Tekst
AliasDomain.analytics=Afwijkende analytics code

SizeGroup=Maattabel
SizeGroup._title=Maattabellen
SizeGroup.name=Maattabel

Size=Maat
Size._title=Maten
Size._add_many=Voeg een aantal maten tegelijk toe
Size.sizegroup=Maattabel
Size.name=Maat

ProductSize=Beschikbare maat
ProductSize._title=Beschikbare maten
ProductSize.product=Product
ProductSize.size=Maat
ProductSize.active=Beschikbaar
ProductSize.sku=Artikelnr
ProductSize.price=Prijs
ProductSize.stock=Voorraad
ProductSize.img=Afbeelding

Voucher=Voucher
Voucher._title=Vouchers
Voucher.barcode=Code
Voucher.value=Waarde
Voucher.percent=Procent
Voucher.date_insert=Datum ingevoerd
Voucher.date_update=Datum aangepast
Voucher.uid=UID
Voucher.order=Bestelling
Voucher.free_shipping=Gratis verzenden
Voucher.free_shipping_above=Gratis verzenden vanaf
Voucher.active=Actief
Voucher.date_exp=Geldig tot

Sidebar=Sidebar
Sidebar._title=Sidebar
Sidebar.active=Zichtbaar
Sidebar.name=Naam
Sidebar.sidebar_type=Type
Sidebar.sidebar_type.text=Tekst
Sidebar.text=Inhoud

Plugin_News_PageNews._title=Nieuws
PageNews._title=Nieuws
PageNews.pubdate=Datum
PageNews.title=Titel
PageNews.link=Link
PageNews.enclosure=Afbeelding
PageNews.description=Tekst
PageNews.is_home=Tonen op homepage
PageNews.is_hot=Uitlichten
PageNews.product_id=Product
PageNews.page_id=Pagina

PageGallery._title=Gallery
PageGallery.pubdate=Datum
PageGallery.title=Titel
PageGallery.link=Link
PageGallery.enclosure=Afbeelding
PageGallery.description=Tekst
PageGallery.is_home=Tonen op homepage
PageGallery.is_hot=Uitlichten
PageGallery.product_id=Product

#----------------------------------------
LINES
);
//end