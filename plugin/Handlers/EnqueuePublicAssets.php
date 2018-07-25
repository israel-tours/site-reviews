<?php

namespace GeminiLabs\SiteReviews\Handlers;

use GeminiLabs\SiteReviews\Application;
use GeminiLabs\SiteReviews\Database\OptionManager;
use GeminiLabs\SiteReviews\Modules\Html;
use GeminiLabs\SiteReviews\Modules\Style;

class EnqueuePublicAssets
{
	/**
	 * @return void
	 */
	public function handle()
	{
		$this->enqueueAssets();
		$this->enqueuePolyfillService();
		$this->enqueueRecaptchaScript();
		$this->inlineStyles();
		$this->localizeAssets();
	}

	/**
	 * @return void
	 */
	public function enqueueAssets()
	{
		if( apply_filters( 'site-reviews/assets/css', true )) {
			wp_enqueue_style(
				Application::ID,
				$this->getStylesheet(),
				[],
				glsr()->version
			);
		}
		if( apply_filters( 'site-reviews/assets/js', true )) {
			$dependencies = apply_filters( 'site-reviews/assets/polyfill', true )
				? [Application::ID.'/polyfill']
				: [];
			$dependencies = apply_filters( 'site-reviews/enqueue/public/dependencies', $dependencies );
			wp_enqueue_script(
				Application::ID,
				glsr()->url( 'assets/scripts/'.Application::ID.'.js' ),
				$dependencies,
				glsr()->version,
				true
			);
		}
	}

	/**
	 * @return void
	 */
	public function enqueuePolyfillService()
	{
		if( !apply_filters( 'site-reviews/assets/polyfill', true ))return;
		wp_enqueue_script(
			Application::ID.'/polyfill',
			'https://cdn.polyfill.io/v2/polyfill.js?features=Element.prototype.closest,Element.prototype.dataset,Event&flags=gated',
			[],
			glsr()->version
		);
	}

	/**
	 * @return void
	 */
	public function enqueueRecaptchaScript()
	{
		if( glsr( OptionManager::class )->get( 'settings.submissions.recaptcha.integration' ) != 'custom' )return;
		$language = apply_filters( 'site-reviews/recaptcha/language', get_locale() );
		wp_enqueue_script( Application::ID.'/google-recaptcha', add_query_arg([
			'hl' => $language,
			'onload' => 'glsr_render_recaptcha',
			'render' => 'explicit',
		], 'https://www.google.com/recaptcha/api.js' ));
		$inlineScript = file_get_contents( glsr()->path( 'assets/scripts/recaptcha.js' ));
		wp_add_inline_script( Application::ID.'/google-recaptcha', $inlineScript, 'before' );
	}

	/**
	 * @return void
	 */
	public function inlineStyles()
	{
		$inlineStylesheetPath = glsr()->path( 'assets/styles/inline-styles.css' );
		if( !apply_filters( 'site-reviews/assets/css', true ))return;
		if( !file_exists( $inlineStylesheetPath )) {
			glsr_log()->error( 'Inline stylesheet is missing: '.$inlineStylesheetPath );
			return;
		}
		$inlineStylesheetValues = glsr()->config( 'inline-styles' );
		$stylesheet = str_replace(
			array_keys( $inlineStylesheetValues ),
			array_values( $inlineStylesheetValues ),
			file_get_contents( $inlineStylesheetPath )
		);
		wp_add_inline_style( Application::ID, $stylesheet );
	}

	/**
	 * @return void
	 */
	public function localizeAssets()
	{
		$variables = [
			'action' => Application::PREFIX.'action',
			'ajaxpagination' => $this->getFixedSelectorsForPagination(),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'validationconfig' => $this->getValidationConfig(),
			'validationstrings' => $this->getValidationStrings(),
		];
		$variables = apply_filters( 'site-reviews/enqueue/public/localize', $variables );
		wp_localize_script( Application::ID, 'GLSR', $variables );
	}

	/**
	 * @return array
	 */
	protected function getFixedSelectorsForPagination()
	{
		$selectors = ['#wpadminbar','.site-navigation-fixed'];
		return apply_filters( 'site-reviews/localize/pagination/selectors', $selectors );
	}

	/**
	 * @return string
	 */
	protected function getStylesheet()
	{
		$currentStyle = glsr( Style::class )->style;
		return file_exists( glsr()->path( 'assets/styles/custom/'.$currentStyle.'.css' ))
			? glsr()->url( 'assets/styles/custom/'.$currentStyle.'.css' )
			: glsr()->url( 'assets/styles/'.Application::ID.'.css' );
	}

	/**
	 * @return array
	 */
	protected function getValidationConfig()
	{
		$defaults = [
			'error_tag' => 'div',
			'error_tag_class' => 'glsr-field-error',
			'field_class' => 'glsr-field',
			'field_error_class' => 'glsr-has-error',
			'input_error_class' => 'glsr-is-invalid',
			'input_success_class' => 'glsr-is-valid',
		];
		$config = array_merge( $defaults, array_filter( glsr( Style::class )->validation ));
		glsr_log( $config );
		return apply_filters( 'site-reviews/localize/validation/config', $config );
	}

	/**
	 * @return array
	 */
	protected function getValidationStrings()
	{
		$strings = [
			'email' => __( 'This field requires a valid e-mail address', 'site-reviews' ),
			'max' => __( 'Maximum value for this field is %s', 'site-reviews' ),
			'maxlength' => __( 'This field length must be < %s', 'site-reviews' ),
			'min' => __( 'Minimum value for this field is %s', 'site-reviews' ),
			'minlength' => __( 'This field length must be > %s', 'site-reviews' ),
			'number' => __( 'This field requires a number', 'site-reviews' ),
			'pattern' => __( 'Input must match the pattern %s', 'site-reviews' ),
			'required' => __( 'This field is required', 'site-reviews' ),
			'tel' => __( 'This field requires a valid telephone number', 'site-reviews' ),
			'url' => __( 'This field requires a valid website URL', 'site-reviews' ),
		];
		return apply_filters( 'site-reviews/localize/validation/strings', $strings );
	}
}
