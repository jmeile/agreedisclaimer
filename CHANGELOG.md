#AgreeDisclaimer (1.0.1) - 07.09.2015
##CHANGES
* Implemented enhacement request:
  - To be able to setup the disclaimer types outside of the code
    https://github.com/jmeile/agreedisclaimer/issues/10
* The disclaimer types are now stored as a json string
* The current selected disclaimer type is no longer stored as a string. It is an
  integer index reffering to the json disclaimer types
* The menu entry will now be rendered with a shorter string. When howering over
  it the longer string will be shown
* The placeholders for the disclaimer texts: %s1 and %s2 were changed by @s1 and
  @s2 because the 'p', 'print_unescaped', and 't' functions from OwnCloud have
  problem with the '%' character
* The function multiple_replace from the JavaScript Utils class was renamed to
  multipleReplace
* A new function: multipleSearch was added to the JavaScript Utils class. This
  function searchs several the keywords in a string and return all their
  positions

#AgreeDisclaimer (1.0.0) - 03.09.2015
##CHANGES
* Implemented enhacement request:
  - Change the whole JavaScript code to OO
    https://github.com/jmeile/agreedisclaimer/issues/9
* Injecting html code by using strings was replaced by jquery objects

#AgreeDisclaimer (0.1.3) - 23.08.2015

##CHANGES
* Added a new application setting to show the disclaimer in the users area. It
  will be rendered either at the top-right or top-left corner.

As a consequense following changes were done:
* Added renderDisclaimerMenu method to the Application class
* Added get_disclaimer_layout route and its dispatcher: getDisclaimerLayout to
  the SettingsController class.
* Added the properties following properties to the Config class:
  - disclaimerLayout: Has the current layout used by the disclaimer in the
    user's area: '' (None), 'top-right', and 'top-left'.
  - disclaimerLayouts: Array containing all possible layouts.
* Added the following methods to the Config class:
  - getDisclaimerLayout: Gets the disclaimer position within the user's page
  - getDisclaimerLayouts: Gets the different layouts in the user area

* Added translations for Portugues (Brazil). Special thanks to Marcelo Subtil
  Marcal for loading them.

#AgreeDisclaimer (0.1.2) - 18.08.2015

##CHANGES
* Fixed: The get_files route was renamed to: get_settings, this was a mistake:
  the get_settings is being used by the login page. The get_files route is
  needed in the admin page when changing the value of the default language
  combobox, so, the get_files route was added again and it will only returned
  the information about the files, but not the rest of the settings.

* Added a new feature to change the disclaimer types; right now we have:
  disclaimer of liability, legal disclaimer, and general terms and conditions.
  This will be reflected in the message of the login page.

#AgreeDisclaimer (0.1.1) - 16.08.2015

##CHANGES
* Added feature to the admin page to allow remembering the last choice of the
  user regarding the disclaimer checkbox. This solution was a feature request:
  https://github.com/jmeile/agreedisclaimer/issues/3

  and it was solved through two cookies: AGChecked (true if the user accepted
  the disclaimer on a previous visit) and AGLastVisit (date of the last visit
  from the user). The last cookie is needed because I also intruduced the
  capability of automatically expire old set cookies. This is useful if the
  disclaimer terms are changed.

  In order to manipulate cookies, the methods: getCookie, setCookie, and
  expireCookie were added. I know there are methods in ownCloud to do this, but
  I did not get them working. I even read the Developer manual, but there it is
  not clear how to set the 'Response' object. I tried several options, but I
  finished doing it with the php functions.

  The respective methods to manipulate these cookies were added:
  getCheckedCookie, setCheckedCookie, expireCheckedCookie, getLastVisitCookie,
  setLastVisitCookie, and expireLastVisitCookie.

  As a consequence, two new four new App settings were added:

  * useCookie: Indicates whether or not use cookies for remembering the checked
    status of the disclaimer.

  * cookieExpTimeIntv: Cookie expiration interval for new cookies. It can be:
    'days', 'weeks', 'months', or 'years'. If left empty, then the cookies will
    not expire.

  * cookieExpTime: Number of cookieExpTimeIntv when the cookies will be expired,
    ie: if cookieExpTimeIntv is 'days' and this is set to 8, cookies will expire
    in eight days.

  * forcedExpDate: Date on which the already set cookies will expire. ie: if the
    user visits the website and agrees the disclaimer the 8th of August, 2015,
    then the 'AGChecked' cookie will be set to true and its expiration time to:
    cookieExpTime + cookieExpTimeIntv. Suppose that the administrator sets now
    this date to 30th of August because the disclaimer terms changed. Now, if
    the user visits the website the 1st of September, then its cookie will be
    invalidated and he will have to agree again to the disclaimer.

  The respective methods: getUseCookie, getCookieExpTime, getCookieExpTimeIntv,
  and getForcedExpDate.

  The getCookieData method of the Config class will be used by the get_settings
  route to get the cookie settigns.

* The feature request: https://github.com/jmeile/agreedisclaimer/issues/4
  was solved by using the 'AGChecked' cookie on the UserHooks class.

* Three new container parameters were added:
  
  * datepickerDateFormat: this is the jquery datepicker format for storing
    dates. It is set to: 'mm/dd/yy'.
  
  * phpDateFormat: this is the php date format used for storing dates and it is
    set to: 'm/d/Y'.
  
  * phpTimeFormat: date and time format used for storing date. It is set to:
    'm/d/Y H:i'.

  As a result, the methods: getDatepickerDateFormat, getPhpDateFormat, and
  getPhpTimeFormat were added to the Config class.

  The convertDateFormatToPhp method was also added to convert datepicker dates
  format to php, ie: 'mm/dd/yy' will be converted to 'm/d/Y'.

  For converting dates on php, the method convertDate was added to the config
  class, ie: the date '08/16/2015', format: 'm/d/Y', will be converted to:
  '16.08.2015', format: 'd.m.Y'. Similarly, a convertDate function was added to
  the utils.js to convert dates in javascript; here the datepicker format will
  be used.

  Please do not change this formats since it will break the application. If you
  want to use a different format for displaying dates, then add the respective
  translation to the string: 'mm/dd/yy' on the l10n file. Here you need to use
  datepicker format and not php.

* Unfortunatelly, ownCloud core lib do not include the datepicker locales, so, I
  had to inject them manually through the datepicker_l10n.js file, which uses
  l10n to translate the strings. It would be easier to use:
  $.datepicker.regional[userLang], but the jquery library from ownCloud do not
  include them :-(

* Renamed the router: settings#get_files to settings#get_settings. Now not only
  the file information from the disclaimer will be returned, but also the cookie
  data. As a consequence, the method getFiles from the SettingsController class
  was renamed to: getSettings

* A setProp method was added to the Config class to set the application
  settings.

#AgreeDisclaimer (0.1.0) - 10.08.2015

##SPECIAL THANKS TO:
* Bernhard Posselt for his suggestions for improving the code quality. Most of
  his ideas were used on this version.

##CHANGES
* Moved the **agreedisclaimer/admin.php** file to **config/admin.php**

* Most of the php code of the file **templates/admin.php** was moved to
  **config/admin.php**

* Check to see if the app is enabled was removed from **app.php**. OwnCloud only
  runs **app.php** if the application is enabled, so the check was redundant.

* Checks for rendering the disclaimer form were removed from **app.php** and
  moved to the **getDisclaimerForm**. Only the call to the function remains
  there.

* Static methods from the **Utils** class were changed to class methods. In
  order to use them, the class needs to be injected into another class'
  constructors.

* Replaced several references to static method: **\OCP\Util::getL10N** by a
  private attribute called **l10n**, which is injected on the constructor.

* The method: **getUserLang** from the utils class was removed. There is already
  the **getLanguageCode** of the **Il10N** interface, which does the same.

* Replaced several references to static method: **\OC::$server->getAppConfig**
  by injecting the **appConfig** from the Application's container or by aquiring
  it directly from it, ie: *$this->getContainer()->getServer()->getAppConfig()*.
  In some cases an **Application** object was also injected.

* Replaced a reference to **\OCP\Util::writeLog** by injecting the logger
  service. 

* Replaced some references to static method: **\OC::$server->getURLGenerator**
  by injecting the **URLGenerator** into the constructor.

* Following class constants where removed from the **Application** class:
  - **APP_ID**: replaced by private attribute **appName**, which can be queried
    with the **getAppName** method.
  - **FILE_PREFFIX**: replaced by container parameter: **filePreffix**.

* Lots of references to **$appId** were renamed to **$appName**. It is more
  corect to speak of a name rather than an id, wich is a number.

* Removed service registration from the **Application** constructor. They must
  be done after the application is created and on the **app.php** file. For
  this, a new function called **registerAll** was added; here the services,
  hooks, and controllers will be registered

* Created **Config** class (agreedisclaimer/config/config.php) to query the
  different app settings. The code that was doing this on the
  **SettingsController** was moved there. The controller will only call the
  **getTxtFileData** and **getPdfFileData** methods from **Config**, which was
  injected through the constructor.

* The application settings had before the application's preffix
  **agreedisclaimer**; now they don't, ie: there was a property called:
  'agreedisclaimerPdfFile', now it is called 'pdfFile'. Only for the html ids,
  the preffix was kept in order to prevent possible conflicts with other
  application settings.

* The boolean application settings, which are stored as strings, are converted
  always to boolean by using a new method of the Config class called:
  **strToBool**.

* **getAvailableLanguages** was moved from the **Application** class to the
  **Utils** class.

* **registerAdmin** call to register the admin page was moved from the
  **Application** class to the a method called **register** inside the
  **Config** class. This method will be called from the **Application** method:
  **registerAll** by using a private class attribute called **config**, which
  was created on its contructor.

* **getAppPath** method from the **Application** class was removed. In order to
  get this path from the **Config** class, dirname(__DIR__) is being used.

* The methods: **getTxtFilesPath** and **getPdfFilesPath** from the
  **Application** class were removed from there and added as container
  parameters in the **Config**  class.

* A new method called **buildPath** was added in the **Config** class.
  This method will take a string array and join it with the path separator of
  the operating system, ie: on linux '/' will be used, while on windows '\' will
  be used. To achive this, the **DIRECTORY_SEPARATOR** php global was used.

* The **get_settings** route was removed. only the **get_files** route was kept.

#AgreeDisclaimer (0.0.1) - 06.08.2015
* First release
