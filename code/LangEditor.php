<?php

class LangEditor extends LeftAndMain {

	static $menu_title = "Lang Editor";
	
	static $url_segment = "langeditor";
	
	static $menu_priority = -0.6;
	
	static $allowed_actions = array (
		'TranslationForm',
		'CreateTranslationForm',
		'show',
		'updatemodules',
		'updatelanguages',
		'updatecreateform',
		'doCreate',
		'doSave'
	);
	
	static $exclude_modules = array();
	static $exclude_locales = array();
	
	static $currentLocale = "";
	static $currentModule = "";
	
/*	public function getResponseNegotiator() {
		$negotiator = parent::getResponseNegotiator();
		$controller = $this;
		// Register a new callback
		$negotiator->setCallback('ModuleList', function() use(&$controller) {
			return $controller->renderWith($controller->getTemplatesWithSuffix('_ModuleList'));
		});
		$negotiator->setCallback('LanguageList', function() use(&$controller) {
			return $controller->renderWith($controller->getTemplatesWithSuffix('_LanguageList'));
		});
		$negotiator->setCallback('CreateTranslationForm', function() use(&$controller) {
			return $controller->renderWith($controller->getTemplatesWithSuffix('_CreateTranslationForm'));
		});
		return $negotiator;
	}
*/	
	public static function get_lang_dir($module) {
		return BASE_PATH.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR."lang";
	}
	
	public static function get_lang_file($module, $lang) {
		$file = self::get_lang_dir($module).DIRECTORY_SEPARATOR."{$lang}.yml";
//		if(!file_exists($file)) user_error("$file does not exist!");
//		if(!is_readable($file)) user_error("$file is not readable!");
//		if(!is_writable($file)) user_error("$file is not writable!");
		return $file;
	}
	
	public static function check_module_existing_lang() {
		if (!is_file(self::get_lang_file(self::$currentModule, self::$currentLocale))) {
			$langs = self::getLanguages();
			self::$currentLocale = $langs->First()->Locale;
		}
	}
	
	public static function get_lang_from_locale($locale) {
		if($str = i18n::get_lang_from_locale($locale)) {
			if(stristr($str,"_") !== false) {
				$parts = explode("_", $str);
				return $parts[0];
			}
			return $str;
		}
		return $locale;
	}
	
	public function init() {
		parent::init();
		
		$r = $this->request; //Controller::curr()->getRequest();
		self::$currentLocale = i18n::get_locale();
		self::$currentModule = project();
		if($r && $r->param('ID') && $r->param('OtherID')) {
			self::$currentLocale = $r->param('ID');
			self::$currentModule = str_replace('$', DIRECTORY_SEPARATOR, $r->param('OtherID'));
		}
		self::check_module_existing_lang();
		
	}
	
/*	public function LinkModuleList() {
		return Controller::join_links(
			$this->Link(),
			'updatemodules',
			self::$currentLocale,
			self::$currentModule
		);
	}
	
	public function LinkLanguageList() {
		return Controller::join_links(
			$this->Link(),
			'updatelanguages',
			self::$currentLocale,
			self::$currentModule
		);
	}
	
	public function LinkCreateTranslationForm() {
		return Controller::join_links(
			$this->Link(),
			'updatecreateform',
			self::$currentLocale,
			self::$currentModule
		);
	}
*/	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function TranslationForm($id = null, $fields = null) {
		$form = new Form(
			$this, 
			"TranslationForm", 
			new FieldList(), 
			new FieldList(
				new FormAction('doSave', _t('LangEditor.SAVE','Save'))
			)
		);
		$form->addExtraClass('cms-edit-form');
		$form->addExtraClass('center ' . $this->BaseCSSClasses());
		$form->setTemplate($this->getTemplatesWithSuffix('_TranslationForm'));
		$form->setAttribute('data-pjax-fragment', 'CurrentForm');
		$form->Namespaces = $this->Namespaces;
		$form->SelectedModule = self::$currentModule;
		$form->unsetValidator();
		return $form;
	}
	
	public function loadTranslationData() {
		$namespaces = new ArrayList();
		$lang_file = self::get_lang_file(self::$currentModule, self::$currentLocale);
				
		// Use the Zend copy of this script to prevent class conflicts when RailsYaml is included
		require_once 'thirdparty/zend_translate_railsyaml/library/Translate/Adapter/thirdparty/sfYaml/lib/sfYaml.php';
		
		$temp_lang = sfYaml::load($lang_file);
		
		$map = array();
		if(is_array($temp_lang) && isset($temp_lang[self::$currentLocale])) {
			foreach($temp_lang[self::$currentLocale] as $namespace => $array_of_entities) {
				$map[$namespace] = $namespace;
				$entities = new ArrayList();
				if(is_array($array_of_entities)) {
					foreach($array_of_entities as $entity => $str) {
						if (is_array($str)) {
							$str = $str[0];
						}
						$entities->push(new ArrayData(array(
							'Entity' => $entity,
							'EntityField' => new TextField("t[".self::$currentLocale."][$namespace][$entity]","",stripslashes($str)),
							'Namespace' => $namespace
						)));
					}
				}
				
				$namespaces->push(new ArrayData(array(
					'Namespace' => $namespace,
					'Entities' => $entities
				)));
			}
		}
		
		$this->Namespaces = $namespaces;
		$this->NamespaceDropdownOptions = $map;

	}
	
	public function Breadcrumbs($unlinked = false) {
		$items = parent::Breadcrumbs(true);
		$items->push(
			new ArrayData(array(
				'Title' => _t('LangEditor.EDITING','Editing').": ".self::$currentModule.", ".self::$currentLocale,
				'Link' => false
			))
		);
		return $items;
	}
	
	
	public static function getLanguages() {
		$langs = new ArrayList();
		if($files = glob(self::get_lang_dir(self::$currentModule).DIRECTORY_SEPARATOR."*.yml")) {
			foreach($files as $file) {
				$label = basename($file,".yml");
				if (!in_array($label, self::$exclude_locales)) {
					$langs->push(new ArrayData(array(
						'Link' => Director::baseURL().'admin/'.self::$url_segment.'/show/'.$label.'/'.str_replace(DIRECTORY_SEPARATOR, '$', self::$currentModule),
						'Locale' => $label,
						'Name' => i18n::get_language_name(self::get_lang_from_locale($label)),
						'Current' => $label == self::$currentLocale ? true : false
					)));
				}
			}
		}
		return $langs;
	}
	
	public function getModules() {
		$modules = new ArrayList();
		
		$folders = scandir(BASE_PATH);
		$themeFolders = array();
		
		foreach($folders as $index => $folder){
			if($folder != 'themes') continue;
			else {
				$themes = scandir(BASE_PATH.DIRECTORY_SEPARATOR."themes");
				if(count($themes)){
					foreach($themes as $theme) {
						if(is_dir(BASE_PATH.DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme) && substr($theme,0,1) != '.' && is_dir(BASE_PATH.DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."templates")){
							$themeFolders[] = 'themes'.DIRECTORY_SEPARATOR.$theme;
						}
					}
				}
				$themesInd = $index;
			}
		}
		if(isset($themesInd)) {
			unset($folders[$themesInd]);
		}
		$folders = array_merge($folders, $themeFolders);
		natcasesort($folders);
				
		foreach($folders as $folder) {
			// Only search for calls in folder with a _config.php file  
			$isValidModuleFolder = (
				!in_array($folder, self::$exclude_modules)
				&& substr($folder,0,1) != '.'
				&& is_dir(BASE_PATH.DIRECTORY_SEPARATOR."$folder")
				&& (
					is_file(BASE_PATH.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR."_config.php") 
					&& file_exists(BASE_PATH.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR."lang")
				) || (
					substr($folder,0,7) == ('themes'.DIRECTORY_SEPARATOR)
					&& file_exists(BASE_PATH.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR."lang")
				)
			);
			if(!$isValidModuleFolder) continue;
			
			$modules->push(new ArrayData(array(
				'Link' => $this->Link("show/".self::$currentLocale."/".str_replace(DIRECTORY_SEPARATOR, '$', $folder)),
				'Name' => $folder,
				'Current' => $folder == self::$currentModule ? true : false
			)));

		}
		return $modules;
	}
	
	public function NamespaceDropdown() {
		if(!$this->NamespaceDropdownOptions) {
			$this->loadTranslationData();
		}
		if($this->NamespaceDropdownOptions) {
			$dropdown = new DropdownField('Namespace', _t('LangEditor.NAMESPACE','Namespace'), $this->NamespaceDropdownOptions);
			$dropdown->setEmptyString('-- '._t('LangEditor.SHOWALLNAMESPACES','Show all namespaces').' --');
			return $dropdown->forTemplate();
		}
		return null;
	}
	
	public function CreateTranslationForm() {
		$from_languages = array();
		$to_languages = i18n::get_common_languages();
		if (dataObject::has_extension('SiteTree', 'Translatable')) {
			$common_languages = $to_languages;
			$to_languages = array();
			foreach(Translatable::get_allowed_locales() as $locale) {
				if (!in_array($locale, self::$exclude_locales)) {
					$language = self::get_lang_from_locale($locale);
					$to_languages[$language] = $common_languages[$language];
				}
			}
		}
		if($languages = $this->getLanguages()) {
			foreach($languages as $l) {
				$from_languages[$l->Locale] = $l->Name;
			}
		}
		$f = new Form(
			$this,
			"CreateTranslationForm",
			new FieldList (
				new DropdownField('LanguageFrom',_t('LangEditor.TRANSLATEFROM','From'), $from_languages, self::$currentLocale),
				$d = new DropdownField('LanguageTo',_t('LangEditor.TRANSLATETO','To'),$to_languages),
				new HiddenField('Module', 'Module', self::$currentModule)
			),
			new FieldList (
				new FormAction('doCreate',_t('LangEditor.CREATE','Create'))
			)
		);
		$d->setEmptyString('-- '._t('LangEditor.PLEASESELECT','Please select').' --');
		return $f;
	}
	
/*	public function getTranslations() {
		$namespaces = new ArrayList();
		$lang_file = self::get_lang_file(self::$currentModule, self::$currentLocale);
		if(!file_exists($lang_file)) return "$lang_file does not exist!";
		if(!is_readable($lang_file)) return "$lang_file is not readable!";
		if(!is_writable($lang_file)) return "$lang_file is not writable!";
		
		// Use the Zend copy of this script to prevent class conflicts when RailsYaml is included
		require_once 'thirdparty/zend_translate_railsyaml/library/Translate/Adapter/thirdparty/sfYaml/lib/sfYaml.php';
		
		$temp_lang = sfYaml::load($lang_file);
		
		$map = array();
		if(is_array($temp_lang) && isset($temp_lang[self::$currentLocale])) {
			foreach($temp_lang[self::$currentLocale] as $namespace => $array_of_entities) {
				$map[$namespace] = $namespace;
				$entities = new ArrayList();
				if(is_array($array_of_entities)) {
					foreach($array_of_entities as $entity => $str) {
						if (is_array($str)) {
							$str = $str[0];
						}
						$entities->push(new ArrayData(array(
							'Entity' => $entity,
							'EntityField' => new TextField("t[".self::$currentLocale."][$namespace][$entity]","",stripslashes($str)),
							'Namespace' => $namespace
						)));
					}
				}
				
				$namespaces->push(new ArrayData(array(
					'Namespace' => $namespace,
					'Entities' => $entities
				)));
			}
		}

		$dropdown = new DropdownField('Namespace', _t('LangEditor.NAMESPACE','Namespace'), $map);
		$dropdown->setEmptyString('-- '._t('LangEditor.SHOWALLNAMESPACES','Show all namespaces').' --');
		return array(
			'NamespaceDropdown' => $dropdown,
			'Namespaces' => $namespaces,
			'SelectedLocale' => self::$currentLocale,
			'SelectedLanguage' => i18n::get_language_name(self::get_lang_from_locale(self::$currentLocale)),
			'SelectedModule' => self::$currentModule
		);
	}
*/	
	public function index($request) {
		self::$currentLocale = i18n::get_locale();
		self::$currentModule = project();
		self::check_module_existing_lang();
		return parent::index($request);
	}
	
	public function show($request) {
		return $this;
	}
	
	public function doSave($data, $form) {
		if(isset($data['t']) && is_array($data['t'])) {
			
			// Use the Zend copy of this script to prevent class conflicts when RailsYaml is included
			require_once 'thirdparty/zend_translate_railsyaml/library/Translate/Adapter/thirdparty/sfYaml/lib/sfYaml.php';
		
			$new_content = sfYaml::dump($data['t'], 99);
			
			$lang = array_keys($data['t']);
			if ($lang) $lang = $lang[0];
			
			$new_file = self::get_lang_file($data['Module'], $lang);
			if($fh = fopen($new_file, "w")) {
				fwrite($fh, $new_content);
				fclose($fh);
				$message = _t('LangEditor.SAVED','Saved');
			} else {
				$message = "Cannot write language file!";
			}			
			
			return new SS_HTTPResponse($message,200);
		}
	}
	
	
	public function doCreate($data, $form) {
		
		$message = _t('LangEditor.CREATED','Created');
		
		self::$currentLocale = $data['LanguageTo'];
		self::$currentModule = $data['Module'];
		
		$to = self::get_lang_file($data['Module'], $data['LanguageTo']);
		$from = self::get_lang_file($data['Module'], $data['LanguageFrom']);
		
		// sanity check
		if(!file_exists($from)) {
			return new SS_HTTPResponse(_t('LangEditor.TOFILEMISSING','The from language does not exist!'),500);
		}
		
		// Use the Zend copy of this script to prevent class conflicts when RailsYaml is included
		require_once 'thirdparty/zend_translate_railsyaml/library/Translate/Adapter/thirdparty/sfYaml/lib/sfYaml.php';
		
		$old_data = sfYaml::load($from);
		
		// get content of source file and replace language
		$new_data = array();
		$new_data[$data['LanguageTo']] = $old_data[$data['LanguageFrom']];
				
		// merge data if target file exists
		if (file_exists($to)) {
			
			$message = _t('LangEditor.MERGED','Merged');
			
			$existing_data = sfYaml::load($to);
			
			if(is_array($existing_data) && isset($existing_data[self::$currentLocale]) 
				&& is_array($new_data) && isset($new_data[self::$currentLocale])) 
			{
				$new_data = array_replace_recursive($new_data, $existing_data);
			}
			
		}
		
		// write new content into target file
		$new_content = sfYaml::dump($new_data, 99);
		$new_file = self::get_lang_file(self::$currentModule, self::$currentLocale);
		if($fh = fopen($new_file, "w")) {
			fwrite($fh, $new_content);
			fclose($fh);
		} else {
			$message = "Cannot write language file!";
		}
		
		//$langs = $this->getLanguages();
		//return $this->customise($langs)->renderWith('LanguageList');
		return new SS_HTTPResponse($message,200);
	}
}