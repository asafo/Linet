
SRCFILES = main.php~ index.php~ menu.inc.php~ calendar.php~ dbarchart.php~ defs.php~ drorit_ltr.css~ drorit.css~ acctadmin.php~ drorit.inc.php~ docnums.php~ items.php~ curradmin.php~ login.php~ docsadmmin.php~ contactup.php~

%.php~: %.php
	./dupload $<
	cp $< $@

%.css~: %.css
	./dupload $<
	cp $< $@

lastupload: $(SRCFILES)
	echo date > lastupload

po: 
	xgettext --from-code=UTF-8 *.php
	msgmerge -U locales/he_IL/LC_MESSAGES/messages.po messages.po
	echo Translate file: locales/he_IL/LC_MESSAGES/messages.po
	echo Then use make messages.mo to upload the file

mo:
	./droritmsgfmt
	./dupload locales/he_IL/LC_MESSAGES/messages.po
	./dupload locales/he_IL/LC_MESSAGES/messages.mo

