<?php
/*
  Plugin Name: Yandex Metrika
  Author: Khakimov Anvar
  Description: Работа с серивисом Яндекс.Метрика.
  Version: 1.0.5
  Requires at least: 3
  License: GPL
  Date: 26.05.2011

 */

$ym = new YM;
$ym->page_title = 'Яндекс.Метрика';
$ym->menu_title = 'Яндекс.Метрика';
$ym->access_level = 'manage_options';

class YM {

	var $page_title;
	var $menu_title;
	var $access_level;

	function __construct() {
		// Admin init
		add_action('admin_init', array(&$this, 'register_admin_settings'));
		add_action('admin_menu', array(&$this, 'add_admin_options'));

		$this->insert_code();
	}

//	function load_options() {
//		$this->options = (array) get_option('yandex_metrika_');
//	}
//
//	function update_options() {
//		return update_option('ym_options', $this->options);
//	}

	/*
	 * ***********************************************************
	 */

	function register_admin_settings() {
		register_setting('yandex_metrika', 'yandex_metrika_', array(&$this, 'validate_options'));
		// Settings fields and sections
		add_settings_section('section_general', 'Основные настройки', array(&$this, 'section_general'), 'yandex_metrika');

		add_settings_field('verification', 'Код подтверждения', array(&$this, 'setting_verification'), 'yandex_metrika', 'section_general');
		add_settings_field('counter', 'Номер счетчика', array(&$this, 'setting_counter'), 'yandex_metrika', 'section_general');
		add_settings_field('location', 'Расположение скрипта', array(&$this, 'setting_location'), 'yandex_metrika', 'section_general');
	}

	function validate_options($options) {
		if(!preg_match('/^\d+$/', $options['counter_id'])) $options['counter_id'] = '';
		return $options;
	}

	function add_admin_options() {
		add_options_page($this->page_title, $this->menu_title, $this->access_level, 'yandex_metrika', array(&$this, 'ym_page'));
	}

	function ym_page() {
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br></div>
			<h2><?php echo $this->page_title ?></h2>

			<form method="post" action="options.php">
				<?php wp_nonce_field('update-options'); ?>
				<?php settings_fields('yandex_metrika'); ?>
				<?php do_settings_sections('yandex_metrika'); ?>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="Сохранить" />
				</p>
			</form>
		</div>
		<?php
	}

	function section_general() {
		echo '';
	}

	function setting_verification() {
		$option = get_option('yandex_metrika_');
		$verification = isset($option['verification']) ? $option['verification'] : '';
		echo "<textarea id='yandex_metrika_verification' name='yandex_metrika_[verification]' rows='2' cols='70'>{$verification}</textarea>\n";
		echo '<span class="description"><pre>&lt;meta content="1234567890000000" name="yandex-verification"&gt;</pre></span>';
	}

	function setting_counter() {
		$option = get_option('yandex_metrika_');
		$counter = isset($option['counter_id']) ? $option['counter_id'] : '';
		echo "<input id='yandex_metrika_counter' name='yandex_metrika_[counter_id]' size='40' type='text' value='{$counter}' />\n";
		echo "<span class='description'><pre>http://metrika.yandex.ru/<span style='border-bottom: 1px dotted;'>somecode</span></pre></span>";
	}

	function setting_location() {
		$option = get_option('yandex_metrika_');
		$footer = isset($option['location']) && $option['location'] == 'footer' ? 'selected=selected' : '';
		$header = isset($option['location']) && $option['location'] == 'header' ? 'selected=selected' : '';
		echo "<select name='yandex_metrika_[location]' id='' style=''>
			<option value='footer' {$footer}>footer</option>
			<!--<option value='header' {$header}>header</option>-->
		</select>\n";
	}

	/*
	 * ***********************************************************
	 */

	function insert_code() {
		$option = get_option('yandex_metrika_');

		if (isset($option['verification']))
			add_action('wp_head', array($this, 'spool_verification'));

		if (isset($option['counter_id'])) {
			if (isset($option['location']) && $option['location'] == 'header')
				add_action('wp_head', array($this, 'spool_metrika'));
			else
				add_action('wp_footer', array($this, 'spool_metrika'));
		}
	}

	/*
	 * @todo spool_metrika
	 */

	function spool_metrika() {
		$option = get_option('yandex_metrika_');
		echo <<<HTML
<!-- Yandex.Metrika counter -->
	<div style="display:none;"><script type="text/javascript">
	(function(w, c) {
	    (w[c] = w[c] || []).push(function() {
		try {
		    w.yaCounter{$option['counter_id']} = new Ya.Metrika({id:{$option['counter_id']}, enableAll: true});
		}
		catch(e) { }
	    });
	})(window, 'yandex_metrika_callbacks');
	</script></div>
	<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
	<noscript><div><img src="//mc.yandex.ru/watch/{$option['counter_id']}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
HTML;
	}

	function spool_verification() {
		$option = get_option('yandex_metrika_');
		echo $option['verification'];
	}

}