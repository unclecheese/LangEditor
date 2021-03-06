# LangEditor Module
Gives a CMS user the ability to change the static language files and translate them into new languages.

## Maintainer Contact

 * Uncle Cheese
   <unclecheese (at) leftandmain (dot) com>

## Requirements

SilverStripe CMS ~3.x-dev / 3.6.x-dev

## Installation
* Download the module
* Extract the downloaded archive into your site root so that the destination folder is called langeditor, opening the extracted folder should contain _config.php in the root along with other files/folders
* Run dev/build?flush=all to regenerate the manifest

## Usage
A new section 'Translator' is added to the CMS. 

You can filter the language files by module and language with the filter options on the left. Once selected the translations are displayed on the right. You can search within the translation using the search field and the namespace dropdown.

You can copy a language file into a new language using the copy function. After copying the file you can translate its content.

If you use Translatable extension, the setting for `Translatable::get_allowed_locales()` is used to determine what languages can be created.

If you use Fluent extension, the setting for `Fluent::locales()` is used to determine what languages can be created.

To hide certain modules or languages from translation add the following to your _config:

```
Config::inst()->update('LangEditor', 'exclude_modules', [
    'cms',
    'framework',
    '[module folder]',
]);

Config::inst()->update('LangEditor', 'exclude_locales', [
    'en_GB',
]);
```

Or via yaml config file:

```
LangEditor:
    exclude_modules:
        - 'cms'
        - 'framework'
        - '[module folder]'
    exclude_locales:
        - 'en_GB'
```

