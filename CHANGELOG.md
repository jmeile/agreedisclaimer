#AgreeDisclaimer (0.0.1) - 06.08.2015
* First release

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
