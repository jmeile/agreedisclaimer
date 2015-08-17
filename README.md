# AgreeDisclaimer
App to show a disclaimer that the user has to agree before accessing OwnCloud.
If the user doesn't check the "agree" checkbox, an error will be displayed and
she/he won't be able to continue until it gets checked.

You can either show a link to open a dialog with the disclaimer text, a link to
a pdf file, or both.

It is also multi-language, so, you can add as many languages as you want and you
can define a default one if the user's language hasn't been translated yet. The
application uses the l10n language service of ownCloud, so, in order to add a
new language, create two files inside: agreedisclaimer/l10n, ie: "fr_BE.js" and
"fr_BE.json"; you will find a template called en.pot with the messages I'm using
in the application. Just add the respective headers and footers to the
translation files and paste that template in the middle of them.

I'm not a native English nor German speaker, so, I would appreciate if you
correct my translations and send me some feedback. I would also appreciate if
you translate it in your own language and send me the text by email.

You can contact me through this form:

https://apps.owncloud.com/messages/?action=newmessage&username=jmeile&PHPSESSID=o79r9jk0oe3ubbqb0c4h7shja4

Or send me an email to: jmeile at hotmail dot com

For reporting issues, please use the tracker:
https://github.com/jmeile/agreedisclaimer/issues

TODO:
* Fix the style sheets so that the pdf icon gets vertical aligned.
* Allow to upload text and pdf files through the admin settings

If you are willing to help me with these three points you can send me the patch
files to my email. I invested lots of time trying to fix the css issue, but I
couldn't figure out how to do it; anyway, it doesn't look that bad :-) 
