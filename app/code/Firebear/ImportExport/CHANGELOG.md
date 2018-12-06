1.5.0
=============
* Fixed bugs:
    * Fixed an issue where strategy validation did not work with value "skip on entries"
* Restructured code for form of Import Jobs:
    * Form at style of Accordeon
* Add features:
    * Add inline edit for field Cron in Grid
    * Add validate of file after entered data for file
* Add Export Jobs:
    * Add grid
    * Add form
    * Add commands
    * Add crontab
    
2.0.0
=============
- general refactoring
- add export jobs similar to import jobs with mapping
- refactoring and improvements for import mapping
- hardcoded values / default values on mapping export 
- Magento 1 pre set for import jobs
- export orders jobs with mapping and filters
- add file validation on import job
- advanced pricing import performance issue
- filtering for export for all entities by attributes
- interaction for default values when should be unique , x1, x2 etc. 
- default / hardcoded value suffix & prefix 
- detailed logging 
- sample files included to extension pack & download from manual 
- unzip / untar file before import 
- upload CSV directly to import job on import form (in web browser)

2.1.0
==============
* Import and Export Fixed Product Tax
* Fix bugs:
   - Hardvalue for field of Entity
   - Load page add Job in Export
   - Import and Export in Admin
   - Correct values for fields of bundle product
   - Check Area Code in Console
   - Delete element in Map
   - Off Logs to Console via Admin
* Add rules for new variations of Configurables Products
* Support Language in console
* Support Language in Cron
* Add Behaviour "Only Update" for Entity of Products
* Add fields for Export's Filter: product_type, attribute_set_code
* Unset special price
* Run re-index command line automatically after import processed
* Import category by Ids and Path of Ids instead of category name
* Generate unique url
* Divide Additional Attributes

2.1.0
==============
* Add Mapping of Categories
* Fix bugs:
   - Cannot set zero value for Default Column in Map Attributes table of Import Job
   - Column of Mapping is Empty after load
   - Cannot change Attribute Set
   - Cannot load file via url
   - Cannot minify js files
    