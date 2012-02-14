<?php

class LangEditor extends LeftAndMain {

	static $menu_title = "Lang Editor";
	
	static $url_segment = "lang-editor";
	
	static $allowed_actions = array (
		'TranslationForm',
		'CreateTranslationForm',
		'show',
		'updatemodules',
		'updatelanguages',
		'updatecreateform'
	);
	
	static $exclude_modules = array();
	
	static $currentLocale = "";
	static $currentModule = "";
	
	public static function lang_dir($module) {
		return BASE_PATH.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR."lang";
	}
	
	public static function lang_file($module, $lang) {
		return self::lang_dir($module).DIRECTORY_SEPARATOR."{$lang}.php";	
	}
	
	public static function clean_namespace($str) {
		return str_replace('.','___',$str);
	}
	
	public static function check_module_existing_lang() {
		if (!is_file(self::lang_file(self::$currentModule, self::$currentLocale))) {
			$langs = self::getLanguages();
			self::$currentLocale = $langs->First()->Locale;
		}
	}
	
	public static function get_lang_from_locale($locale) {
		if($str = i18n::get_lang_from_locale($locale)) {
			if(stristr($str,"_") !== false) {
				$parts = explode("_", $str);
				return $parts[0]."-".strtoupper($parts[1]);
			}
			return $str;
		}
		return $locale;
	}
	
	public function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");

		Requirements::javascript("lang_editor/javascript/lang_editor.js");
		Requirements::css("lang_editor/css/lang_editor.css");
	}
	
	public function getLanguages() {
		$langs = new DataObjectSet();
		if($files = glob(self::lang_dir(self::$currentModule).DIRECTORY_SEPARATOR."*.php")) {
			foreach($files as $file) {
				$label = basename($file,".php");
					$langs->push(new ArrayData(array(
						'Link' => isset($this) ? $this->Link("show/$label/".str_replace(DIRECTORY_SEPARATOR, '$', self::$currentModule)): '',
						'Locale' => $label,
						'Name' => i18n::get_language_name(self::get_lang_from_locale($label))
					)));
			}
		}
		return $langs;		
	}
	
	public function getModules() {
		$modules = new DataObjectSet();
		
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
				'Name' => $folder
			)));

		}
		return $modules;		
	}
	
	public function TranslationForm() {
		return new Form (
			$this,
			"TranslationForm",
			new FieldSet(),
			new FieldSet(
				new FormAction('doSave', _t('LangEditor.SAVE','Save'))
			)
		);
	}
	
	public function NamespaceDropdown() {
		if($this->Namespaces) {
			$map = array();
			foreach($this->Namespaces as $n) {
				$map[$n->Namespace][$n->Namespace];
			}
		}
	}
	
	public function CreateTranslationForm() {
		$from_languages = array();
		$to_languages =  i18n::get_common_locales();
		if($languages = $this->getLanguages()) {
			foreach($languages as $l) {
				$from_languages[$l->Locale] = $l->Name;
				if(isset($to_languages[$l->Locale])) {
					unset($to_languages[$l->Locale]);
				}
			}
		}
		$f = new Form(
			$this,
			"CreateTranslationForm",
			new FieldSet (
				new DropdownField('LanguageFrom',_t('LangEditor.TRANSLATEFROM','From'), $from_languages, i18n::get_locale()),
				$d = new DropdownField('LanguageTo',_t('LangEditor.TRANSLATETO','To'),$to_languages),
				new HiddenField('Module', 'Module', self::$currentModule)
			),
			new FieldSet (
				new FormAction('doCreate',_t('LangEditor.CREATE','Create'))
			)
		);
		$d->setEmptyString('-- '._t('LangEditor.PLEASESELECT','Please select').' --');
		return $f;
	}
	
	public function getTranslations() {
		$namespaces = new DataObjectSet();
		$lang_file = self::lang_file(self::$currentModule, self::$currentLocale);
		if(!file_exists($lang_file)) return "$lang_file does not exist!";
		if(!is_readable($lang_file)) return "$lang_file is not readable!";
		if(!is_writable($lang_file)) return "$lang_file is not writable!";
		
		$code = file_get_contents($lang_file)." return \$lang;";
		$code = str_replace(
			array('<?php','<?','?>','global $lang;'), 
			array('','','','$lang = array();'),
			$code
		);

		$map = array();
		$temp_lang = eval($code);
		if(is_array($temp_lang) && isset($temp_lang[self::$currentLocale])) {
			foreach($temp_lang[self::$currentLocale] as $namespace => $array_of_entities) {
				$map[self::clean_namespace($namespace)] = $namespace;
				$entities = new DataObjectSet();
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
					'NamespaceID' => self::clean_namespace($namespace),
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
	
	public function index() {
		self::$currentLocale = i18n::get_locale();
		self::$currentModule = project();
		self::check_module_existing_lang();
		return $this->getTranslations();
	}
	
	public function show(SS_HTTPReqest $r) {
		if(!self::$currentLocale = $r->param('ID')) {
			return $this->httpError(404);
		}
		if(!self::$currentModule = str_replace('$', DIRECTORY_SEPARATOR, $r->param('OtherID'))) {
			return $this->httpError(404);
		}
		self::check_module_existing_lang();
		$data = $this->getTranslations();
		return $this->customise($data)->renderWith('Translations');
	}
	
	public function updatemodules(SS_HTTPReqest $r) {
		if(!self::$currentLocale = $r->param('ID')) {
			return $this->httpError(404);
		}
		if(!self::$currentModule = str_replace('$', DIRECTORY_SEPARATOR, $r->param('OtherID'))) {
			return $this->httpError(404);
		}
		self::check_module_existing_lang();
		$data = $this->getModules();
		return $this->customise($data)->renderWith('ModuleList');
	}
	
	public function updatelanguages(SS_HTTPReqest $r) {
		if(!self::$currentLocale = $r->param('ID')) {
			return $this->httpError(404);
		}
		if(!self::$currentModule = str_replace('$', DIRECTORY_SEPARATOR, $r->param('OtherID'))) {
			return $this->httpError(404);
		}
		self::check_module_existing_lang();
		$data = $this->getLanguages();
		return $this->customise($data)->renderWith('LanguageList');
	}
	
	public function updatecreateform(SS_HTTPReqest $r) {
		if(!self::$currentLocale = $r->param('ID')) {
			return $this->httpError(404);
		}
		if(!self::$currentModule = str_replace('$', DIRECTORY_SEPARATOR, $r->param('OtherID'))) {
			return $this->httpError(404);
		}
		self::check_module_existing_lang();
		$data = $this->CreateTranslationForm();
		return $this->customise($data)->renderWith('CreateTranslationForm');
	}
	
	public function doSave($data, $form) {
		if(isset($data['t']) && is_array($data['t'])) {
			$output = "<?php\n\nglobal \$lang;\n\n";
			foreach($data['t'] as $locale => $array_of_namespaces) {
				foreach($array_of_namespaces as $namespace => $array_of_entities) {
					foreach($array_of_entities as $entity => $translation) {
						$output .= "\$lang['$locale']['$namespace']['$entity'] = '".addslashes($translation)."';\n";
					}
				}			
			}
			$fh = fopen(self::lang_file($data['Module'], reset(array_keys($data['t']))),"w");
			fwrite($fh, $output);
			fclose($fh);
			return new SS_HTTPResponse(_t('LangEditor.SAVED','Saved'),200);
		}
	}
	
	
	public function doCreate($data, $form) {
		
		self::$currentLocale = $data['LanguageTo'];
		self::$currentModule = $data['Module'];
		
		$to = self::lang_file($data['Module'], $data['LanguageTo']);
		$from = self::lang_file($data['Module'], $data['LanguageFrom']);
		
		// sanity check
		if(!file_exists($from) || file_exists($to)) {
			return new SS_HTTPResponse(_t('LangEditor.FILESNOTRIGHT','The from language does not exist, or the new language already exists!'),500);
		}
		
		$new_contents = str_replace(
			"['".$data['LanguageFrom']."']",
			"['".$data['LanguageTo']."']",
			file_get_contents($from)
		);
		$fh = fopen($to,"w");
		fwrite($fh, $new_contents);
		fclose($fh);
		
		$langs = $this->getLanguages();
		return $this->customise($langs)->renderWith('LanguageList');
		//return $this->renderWith('LanguageList');
	}
}