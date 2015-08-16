/**
  * Fix it: This is an ugly hack, but since OwnCloud didn't include the
  * jquery datepicker locales, I had to inject my own. I know that there are
  * some applications (calendar and tasks)  that have this settings, but I don't
  * want to depend on them. Better would be to have this on OwnCloud's core
  * (just include the jquery ui datepicker locales)
  *
  * If you want to translate your own language, see the jquery.ui.datepicker-*
  * files inside a jquery distribution. You will have to extract the strings
  * from there and paste them into the respective l10n file
  */
function initDatePickerLocale(appName, userLang) {
    $.datepicker.regional[userLang] = {
        closeText: t(appName, 'Done'),
        prevText: '&laquo;' + t(appName, 'Prev'),
        nextText: t(appName, 'Next') + '&raquo;',
        currentText: t(appName, 'Today'),
        
        //The OwnCloude core already has this
        monthNames: monthNames, 
        //Taken from: owncloud/core/js/share.js
        monthNamesShort: $.map(monthNames, function(v) {
                             return v.slice(0,3)+'.';
                         }
        ),
        //The OwnCloude core already has this
        dayNames: dayNames, 
        //Taken from: owncloud/core/js/share.js
        dayNamesShort: $.map(dayNames, function(v) {
                           return v.slice(0,3); 
                       }
        ),
        //Taken from: owncloud/core/js/share.js
        dayNamesMin: $.map(dayNames, function(v) {
                         return v.slice(0,2);
                     }
        ), 
        weekHeader: t(appName, 'Wk'),

        //Do not change this. It will be got from the l10n file
        dateFormat: t(appName, 'mm/dd/yy'),
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
}
